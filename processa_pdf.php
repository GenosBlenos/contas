<?php
session_start();

// 1. INCLUIR ARQUIVOS NECESSÁRIOS
// ===============================
require_once __DIR__ . '/app/conexao.php'; // Usando a conexão PDO
require 'vendor/autoload.php';
use Smalot\PdfParser\Parser;

// Função auxiliar para redirecionar com uma mensagem de forma segura e consistente.
function redirectWithMessage($message, $location = 'cad_fatura_pdf.php') {
    $_SESSION['msg'] = $message;
    header("Location: {$location}");
    exit();
}

// 2. VERIFICAR O UPLOAD DO ARQUIVO
// ==================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
    redirectWithMessage('<p style="color:red;">Erro: Nenhum arquivo foi enviado ou ocorreu um erro no upload.</p>');
}

$pdfFilePath = $_FILES['pdfFile']['tmp_name'];

    // 3. EXTRAIR TEXTO DO PDF
    // ========================
    try {
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfFilePath);
        $text = $pdf->getText();
    } catch (Exception $e) {
        redirectWithMessage("<p style='color:red;'>Erro ao ler o arquivo PDF: " . htmlspecialchars($e->getMessage()) . "</p>");
    }

    $pdfFileName = $_FILES['pdfFile']['name'];

    // 4. CLASSIFICAR O TIPO DE FATURA USANDO A API DE ML
    // ===================================================
    $apiUrl = 'http://127.0.0.1:5000/predict';
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text' => $text]));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch); // Get cURL error message if any
    curl_close($ch);

    // First, check for a cURL-level error (e.g., connection refused)
    if ($response === false) {
        redirectWithMessage("<p style='color:red;'>Erro de cURL ao comunicar com o serviço: " . htmlspecialchars($curlError) . "</p>");
    }

    // Next, check for a non-200 HTTP status code (e.g., 404 Not Found, 500 Server Error)
    if ($httpCode != 200) {
        $errorMessage = "<p style='color:red;'>Erro ao comunicar com o serviço de classificação de faturas.</p>";
        $errorMessage .= "<p style='color:red;'>Código de Status HTTP: " . htmlspecialchars($httpCode) . "</p>";
        if (!empty($response)) {
            $errorMessage .= "<div style='color:red; border: 1px solid red; padding: 10px; margin-top: 10px;'><b>Resposta do Serviço:</b><pre>" . htmlspecialchars($response) . "</pre></div>";
        }
        redirectWithMessage($errorMessage);
    }

    $result = json_decode($response, true);
    $category = $result['category'] ?? 'desconhecido';

    // 5. EXTRAIR DADOS E INSERIR NA TABELA CORRETA
    // =================================================
    // Agora, com a categoria, podemos usar a lógica de extração correta.
    try {
        $dados = [];
        $tabela = '';

        switch ($category) {
            case 'agua':
                $tabela = 'agua';
                // Regex para contas de água (exemplo)
                preg_match('/Consumo\s+m³\s+([\d,\.]+)/i', $text, $m);
                $dados['consumo'] = (float) str_replace(',', '.', $m[1] ?? '0');
                preg_match('/Total\s+a\s+Pagar\s+R\$\s*([\d,\.]+)/i', $text, $m);
                $dados['valor'] = (float) str_replace(',', '.', $m[1] ?? '0');
                preg_match('/Vencimento\s+(\d{2}\/\d{2}\/\d{4})/i', $text, $m);
                $dateObj = !empty($m[1]) ? DateTime::createFromFormat('d/m/Y', $m[1]) : false;
                $dados['data_vencimento'] = $dateObj ? $dateObj->format('Y-m-d') : null;
                break;

            case 'energia':
                $tabela = 'energia';
                // Regex para contas de energia (exemplo)
                preg_match('/Consumo\s+kWh\s+([\d,\.]+)/i', $text, $m);
                $dados['consumo'] = (float) str_replace(',', '.', $m[1] ?? '0');
                preg_match('/Valor\s+a\s+Pagar\s+R\$\s*([\d,\.]+)/i', $text, $m);
                $dados['valor'] = (float) str_replace(',', '.', $m[1] ?? '0');
                preg_match('/Data\s+do\s+Vencimento\s+(\d{2}\/\d{2}\/\d{4})/i', $text, $m);
                $dateObj = !empty($m[1]) ? DateTime::createFromFormat('d/m/Y', $m[1]) : false;
                $dados['data_vencimento'] = $dateObj ? $dateObj->format('Y-m-d') : null;
                break;

            case 'telefone':
                $tabela = 'telefone';
                // Regex para contas de telefone (exemplo)
                // Tenta capturar o número do telefone
                preg_match('/(?:Telefone|N[ú|º]mero):\s*([\d\s\(\)-]+)/i', $text, $m);
                $dados['numero'] = trim($m[1] ?? 'N/A');

                // Tenta capturar o valor total (regex mais genérica)
                preg_match('/(?:Total\s+a\s+Pagar|VALOR\s+TOTAL)\s+R?\$\s*([\d,\.]+)/i', $text, $m);
                $dados['valor'] = (float) str_replace(',', '.', $m[1] ?? '0');

                // Tenta capturar a data de vencimento
                preg_match('/Vencimento\s*:?\s*(\d{2}\/\d{2}\/\d{4})/i', $text, $m);
                $dateObj = !empty($m[1]) ? DateTime::createFromFormat('d/m/Y', $m[1]) : false;
                $dados['data_vencimento'] = $dateObj ? $dateObj->format('Y-m-d') : null;
                break;

            default:
                // Se não for uma categoria conhecida, pode salvar na tabela genérica como fallback
                redirectWithMessage("<p style='color:orange;'>Categoria de fatura '" . htmlspecialchars($category) . "' não reconhecida.</p>");
        }

        if ($tabela && !empty($dados)) {
            // Preenche dados que faltam
            $dados['criado_por'] = $_SESSION['usuario_id'] ?? null;
            $dados['observacoes'] = "Cadastrado via PDF: " . $pdfFileName;

            // Validação final: se dados essenciais não foram encontrados, informa o usuário.
            if (empty($dados['valor']) || empty($dados['data_vencimento'])) {
                throw new Exception("Não foi possível extrair o VALOR ou a DATA DE VENCIMENTO do PDF. Verifique o documento.");
            }

            $colunas = implode(', ', array_keys($dados));
            $placeholders = ':' . implode(', :', array_keys($dados));

            $sql = "INSERT INTO {$tabela} ({$colunas}) VALUES ({$placeholders})";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute($dados)) {
                $successMessage = "<p style='color:green;'>Fatura de '" . htmlspecialchars($category) . "' cadastrada com sucesso a partir do PDF!</p>";
                redirectWithMessage($successMessage);
            } else {
                throw new Exception("Erro ao executar a inserção no banco de dados.");
            }
        } else {
            // Isso acontece se a categoria for reconhecida, mas as regex falharem.
            throw new Exception("Não foi possível extrair dados para a categoria '" . htmlspecialchars($category) . "'. Verifique as expressões regulares (regex).");
        }

    } catch (Exception $e) {
        redirectWithMessage('<p style="color:red;">Erro ao processar dados: ' . htmlspecialchars($e->getMessage()) . '</p>');
    }
?>