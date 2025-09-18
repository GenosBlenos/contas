<?php
require_once __DIR__ . '/src/includes/auth.php';
include "./src/includes/header.php";

$pageTitle = 'Ajuda';

$content = <<<HTML
    <div class="space-y-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Como Usar o Sistema</h2>
            <div class="prose max-w-none">
                <p class="text-gray-600">Este sistema foi desenvolvido para ajudar no controle e gerenciamento de despesas da prefeitura. Abaixo você encontrará instruções sobre como utilizar cada módulo:</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Água -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center mb-4">
                    <img src="./assets/water.png" alt="Água" class="w-8 h-8 mr-3">
                    <h3 class="text-lg font-semibold text-gray-800">Água Predial</h3>
                </div>
                <p class="text-gray-600">Módulo para controle das contas de água dos prédios públicos. Registre consumo, valores e mantenha um histórico completo.</p>
            </div>

            <!-- Energia -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center mb-4">
                    <img src="./assets/flash.png" alt="Energia" class="w-8 h-8 mr-3">
                    <h3 class="text-lg font-semibold text-gray-800">Energia Elétrica</h3>
                </div>
                <p class="text-gray-600">Controle do consumo de energia elétrica, incluindo demanda contratada e consumo em horário de ponta.</p>
            </div>

            <!-- Sem Parar -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center mb-4">
                    <img src="./assets/car.png" alt="Sem Parar" class="w-8 h-8 mr-3">
                    <h3 class="text-lg font-semibold text-gray-800">Sem Parar</h3>
                </div>
                <p class="text-gray-600">Controle de despesas com pedágios e estacionamentos dos veículos oficiais equipados com Sem Parar.</p>
            </div>

            <!-- Telefone -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center mb-4">
                    <img src="./assets/phone.png" alt="Telefone" class="w-8 h-8 mr-3">
                    <h3 class="text-lg font-semibold text-gray-800">Telefonia Fixa</h3>
                </div>
                <p class="text-gray-600">Gerenciamento das linhas telefônicas, controle de gastos e monitoramento do consumo por setor.</p>
            </div>
        </div>

        <!-- Suporte -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Precisa de Ajuda?</h2>
            <div class="prose max-w-none">
                <p class="text-gray-600">Em caso de dúvidas ou problemas, entre em contato com o suporte técnico:</p>
                <ul class="list-disc list-inside mt-4 space-y-2 text-gray-600">
                    <li>Email: programacao.ti@salto.sp.gov.br</li>
                    <li>Telefone: (11) 4602-8500 (Ramal 202)</li>
                    <li>Horário de atendimento: Segunda a Sexta, das 8h às 17h</li>
                </ul>
            </div>
        </div>
    </div>
HTML;

require_once './src/includes/template.php';
?>
