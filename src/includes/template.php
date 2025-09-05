<?php
require_once __DIR__ . '/auth.php';
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: login.php');
    exit;
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="index.php" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#147cac]">
                    <img src="./assets/home.png" alt="Home" class="w-4 h-4 mr-2">
                    Início
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2"><?php echo $pageTitle ?? 'Página'; ?></span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Content -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $pageTitle ?? 'Página'; ?></h1>
        
        <?php if (isset($content)): ?>
            <?php echo $content; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once './src/includes/footer.php'; ?>
