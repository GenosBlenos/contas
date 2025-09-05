<?php

// Helpers para Formatação
function formatarMoeda(float $valor): string {
    return number_format($valor, 2, ',', '.');
}

function formatarData(string $data): string {
    return date('d/m/Y', strtotime($data));
}

// Helpers para Segurança e Validação
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = preg_replace('/\s+/', ' ', trim($data));
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function validarPermissao(string $permissao): void {
    if (!isset($_SESSION['permissoes']) || !in_array($permissao, $_SESSION['permissoes'])) {
        header('Location: unauthorized.php');
        exit;
    }
}

function validarCNPJ(string $cnpj): bool {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    if (strlen($cnpj) != 14) return false;
    if (preg_match('/(\d)\1{13}/', $cnpj)) return false;
    
    // Validação do primeiro dígito verificador
    $soma = 0;
    for ($i = 0, $j = 5; $i < 12; $i++) {
        $soma += (int)$cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    $resto = $soma % 11;
    $digito1 = ($resto < 2) ? 0 : 11 - $resto;
    if ((int)$cnpj[12] !== $digito1) return false;
    
    // Validação do segundo dígito verificador
    $soma = 0;
    for ($i = 0, $j = 6; $i < 13; $i++) {
        $soma += (int)$cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    $resto = $soma % 11;
    $digito2 = ($resto < 2) ? 0 : 11 - $resto;
    return (int)$cnpj[13] === $digito2;
}

function validarCPF(string $cpf): bool {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11) return false;
    if (preg_match('/(\d)\1{10}/', $cpf)) return false;
    
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int)$cpf[$i] * (($t + 1) - $i);
        }
        $resto = ($soma * 10) % 11;
        if ($resto == 10 || $resto == 11) {
            $resto = 0;
        }
        if ($resto != (int)$cpf[$t]) {
            return false;
        }
    }
    
    return true;
}

function validarSenha(string $senha): bool {
    // Mínimo 8 caracteres, pelo menos uma letra maiúscula, uma minúscula e um número
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $senha);
}


// Helpers para Mensagens e Sessão
function flashMessage(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessages(): array {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

function isActive(string $pagina): string {
    $current_page = basename($_SERVER['PHP_SELF']);
    return $current_page === $pagina ? 'active' : '';
}

// Helpers para Máscaras
function mascaraCPF(string $cpf): string {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) === 11) {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }
    return $cpf;
}

function mascaraCNPJ(string $cnpj): string {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    if (strlen($cnpj) === 14) {
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }
    return $cnpj;
}

function mascaraTelefone(string $telefone): string {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    $length = strlen($telefone);
    
    if ($length === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } else if ($length === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    
    return $telefone;
}

// Helpers para Lógica de Negócio
function calcularMedia(array $valores): float {
    if (empty($valores)) return 0;
    return array_sum($valores) / count($valores);
}

function calcularTotal(array $valores): float {
    return (float)array_sum($valores);
}

function slug(string $string): string {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function limparString(string $string): string {
    return preg_replace('/[^a-zA-Z0-9]/', '', $string);
}

// Helpers para Integrações e Logs
function buscarCEP(string $cep): array {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    if (strlen($cep) !== 8) {
        return ['erro' => true, 'mensagem' => 'CEP inválido.'];
    }
    
    $url = "https://viacep.com.br/ws/{$cep}/json/";
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return ['erro' => true, 'mensagem' => 'Erro na requisição cURL: ' . $error_msg];
    }
    curl_close($ch);
    
    if ($http_code !== 200) {
        return ['erro' => true, 'mensagem' => 'Erro na resposta do servidor (HTTP ' . $http_code . ').'];
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['erro' => true, 'mensagem' => 'Resposta inválida do servidor (JSON).'];
    }
    
    if (isset($data['erro']) && $data['erro'] === true) {
        return ['erro' => true, 'mensagem' => 'CEP não encontrado.'];
    }
    
    return $data;
}

function enviarEmail(string $para, string $assunto, string $mensagem): bool {
    // Configurar envio de e-mail (implementar conforme necessidade)
    return mail($para, $assunto, $mensagem);
}

function gerarPDF(string $html, string $filename = 'documento.pdf'): bool {
    // Implementar geração de PDF (pode usar library como DOMPDF)
    return true;
}

function gerarLog(string $acao, string $descricao): void {
    $log = date('Y-m-d H:i:s') . " | {$acao} | {$descricao}\n";
    file_put_contents(__DIR__ . '/../logs/sistema.log', $log, FILE_APPEND);
}

function gerarGrafico(array $dados, string $tipo = 'line') {
    // Implementar lógica de geração de gráficos
    return json_encode($dados);
}