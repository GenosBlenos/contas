<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Controle de Gastos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <header class="bg-[#147cac] text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div style="flex:1; display:flex; justify-content:flex-start; min-width:128px;">
                <a href="/compras/index.php">
                    <img src="/compras/assets/logo-prefeitura-hd.png" alt="Prefeitura de Salto"
                        style="max-width:100%; height:auto; width:auto; max-height:64px;">
                </a>
            </div>
            <?php if (isset($_SESSION['logado']) && $_SESSION['logado']): ?>
                <div class="flex items-center space-x-4">
                    <span>Olá, <?php echo htmlspecialchars($_SESSION['nome']); ?></span>
                    <a href="logout.php" class="flex items-center space-x-2 hover:text-gray-300">
                        <img src="./assets/log-out.png" alt="Sair" class="h-6">
                        <span>Sair</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <?php
    require_once __DIR__ . '/nav.php'; // Inclui o novo arquivo de navegação
    ?>
    <main class="container mx-auto px-4 py-8">