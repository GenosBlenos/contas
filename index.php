<?php
// 1. Ponto de Entrada e Autenticação
require_once __DIR__ . '/src/includes/auth.php';
// A classe SecurityManager depende da classe Logger, então Logger.php precisa ser incluído antes.
require_once __DIR__ . '/src/includes/Logger.php';
require_once __DIR__ . '/src/includes/SecurityManager.php';

$securityManager = SecurityManager::getInstance();

if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: login.php');
    exit;
}

// Páginas que são arquivos .php independentes na raiz.
$standalonePages = [
    'agua',
    'energia',
    'semparar',
    'telefone',
    'support',
    'cad_fatura_pdf',
];

$page = $_GET['page'] ?? 'dashboard'; // A página padrão é o dashboard

// Se a página solicitada for uma página autônoma, redireciona para o arquivo .php correspondente.
if (in_array($page, $standalonePages)) {
    header('Location: ' . $page . '.php');
    exit;
}

// Define o módulo para que o header e o nav possam usá-lo
$_GET['module'] = $page;

require_once __DIR__ . '/src/includes/header.php';

// 3. Tratamento de Requisições POST (Ações de Formulário)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação de Token CSRF
    if (!isset($_POST['csrf_token']) || !$securityManager->validateCSRF($_POST['csrf_token'])) {
        // Log do erro já é feito dentro do SecurityManager
        // Pode redirecionar para uma página de erro ou a página anterior com uma mensagem
        $_SESSION['error'] = 'Erro de validação de segurança. Por favor, tente novamente.';
        header('Location: index.php?page=' . $page);
        exit;
    }

    if (isset($controllers[$page])) {
        $controllerName = $controllers[$page];
        $controllerPath = __DIR__ . '/src/controllers/' . $controllerName . '.php';

        if (file_exists($controllerPath)) {
            require_once $controllerPath;
            $controller = new $controllerName();

            $action = $_POST['action'] ?? '';
            if (method_exists($controller, $action)) {
                $controller->$action(); // Executa a ação (store, update, destroy)
            } else {
                // Ação não encontrada, redireciona ou mostra erro
                header('Location: index.php?page=' . $page . '&error=invalid_action');
                exit;
            }
        } else {
            // Controller não encontrado para a página da requisição POST
            header('Location: index.php?error=not_found');
            exit;
        }
    } else {
        // Página não encontrada para a requisição POST
        header('Location: index.php?error=not_found');
        exit;
    }
    // A execução termina aqui para POSTs, pois os controllers redirecionam
}

// 4. Tratamento de Requisições GET (Exibição de Páginas)
$csrfToken = $securityManager->getCSRFToken();
$pageTitle = ucfirst($page);

// Carrega o controller correspondente à página para buscar dados
if (isset($controllers[$page])) {
    $controllerName = $controllers[$page];
    $controllerPath = __DIR__ . '/src/controllers/' . $controllerName . '.php';

    if (file_exists($controllerPath)) {
        require_once $controllerPath;
        $controller = new $controllerName();
        
        if (method_exists($controller, 'index')) {
            $data = $controller->index();
            extract($data);
        }
    }
    // O view correspondente será incluído abaixo

} else if ($page !== 'dashboard') {
    // Se a página não é um controller conhecido e não é o dashboard, redireciona
    // Ou podemos mostrar uma página 404 dedicada
    // Por enquanto, redirecionamos para o dashboard
    header('Location: index.php?page=dashboard&error=not_found');
    exit;
}

// 5. Renderização da View
$viewPath = __DIR__ . '/src/views/' . $page . '/index.php';

if (file_exists($viewPath)) {
    require_once $viewPath;
} else {
    // Se a view não existe, carrega o dashboard como padrão
    $pageTitle = 'Dashboard';
    require_once __DIR__ . '/src/views/dashboard/index.php';
}
