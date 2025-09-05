<?php
require_once __DIR__ . '/src/includes/auth.php';
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: login.php');
    exit;
}
include "./src/includes/header.php";
?>
    <div class="container">
        <div class="mt-8 mb-12 text-center">
            <p class="text-2xl font-semibold text-[#22223b]">Sistema de Controle de Gastos</p>
        </div>
        <div class="grid-menu flex flex-wrap justify-center gap-10 mb-8">
            <a class="menu-item bg-white border-2 border-gray-300 rounded-2xl shadow p-8 min-w-[250px] min-h-[250px] max-w-[340px] flex flex-col items-center justify-center text-center transition hover:border-[#4a90e2] hover:shadow-lg" href="./agua.php">
                <img src="./assets/water.png" alt="Água" class="w-[140px] h-[100px] object-contain mb-4">
                <div class="menu-label text-lg font-medium text-[#22223b]">Água Predial</div>
            </a>
            <a class="menu-item bg-white border-2 border-gray-300 rounded-2xl shadow p-8 min-w-[250px] min-h-[250px] max-w-[340px] flex flex-col items-center justify-center text-center transition hover:border-[#4a90e2] hover:shadow-lg" href="./energia.php">
                <img src="./assets/flash.png" alt="Energia" class="w-[140px] h-[100px] object-contain mb-4">
                <div class="menu-label text-lg font-medium text-[#22223b]">Energia Elétrica</div>
            </a>
            <a class="menu-item bg-white border-2 border-gray-300 rounded-2xl shadow p-8 min-w-[250px] min-h-[250px] max-w-[340px] flex flex-col items-center justify-center text-center transition hover:border-[#4a90e2] hover:shadow-lg" href="./semparar.php">
                <img src="./assets/car.png" alt="Sem Parar" class="semparar-img w-[120px] max-w-[80vw] h-[60px] object-contain mb-2">
                <div class="menu-label text-lg font-medium text-[#22223b]">Sem Parar</div>
            </a>
            <a class="menu-item bg-white border-2 border-gray-300 rounded-2xl shadow p-8 min-w-[250px] min-h-[250px] max-w-[340px] flex flex-col items-center justify-center text-center transition hover:border-[#4a90e2] hover:shadow-lg" href="./telefone.php">
                <img src="./assets/phone.png" alt="Telefone" class="w-[140px] h-[100px] object-contain mb-4">
                <div class="menu-label text-lg font-medium text-[#22223b]">Telefonia Fixa</div>
            </a>
        </div>
        <div class="ajuda-container pt-40 pb-5 flex justify-center">
            <a href="./support.php" style="text-decoration:none;">
                <button class="ajuda-btn flex items-center gap-3 bg-[#4a90e2] text-white rounded-2xl px-8 py-3 text-lg font-medium shadow hover:bg-[#2563eb] transition">
                    <img src="./assets/support.png" alt="Ajuda" class="w-8 h-8">
                    Ajuda
                </button>
            </a>
        </div>
    </div>
<?php
include "./src/includes/footer.php";
?>
