<?php
session_start();
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Estagiário - Guardinha';

// Aqui virá a lógica de banco de dados para estagiários

$content = <<<HTML
    <div class="space-y-6">
        <!-- Formulário de Cadastro/Edição -->
        <form action="" method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700">Nome do Estagiário</label>
                    <input type="text" name="nome" id="nome" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>

                <div>
                    <label for="data_inicio" class="block text-sm font-medium text-gray-700">Data de Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>

                <div>
                    <label for="data_fim" class="block text-sm font-medium text-gray-700">Data de Término</label>
                    <input type="date" name="data_fim" id="data_fim"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>

                <div>
                    <label for="valor_bolsa" class="block text-sm font-medium text-gray-700">Valor da Bolsa</label>
                    <input type="number" name="valor_bolsa" id="valor_bolsa" step="0.01" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>

                <div>
                    <label for="setor" class="block text-sm font-medium text-gray-700">Setor</label>
                    <input type="text" name="setor" id="setor" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                        <option value="">Selecione...</option>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                        <option value="finalizado">Contrato Finalizado</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="observacoes" class="block text-sm font-medium text-gray-700">Observações</label>
                <textarea name="observacoes" id="observacoes" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50"></textarea>
            </div>

            <div class="flex justify-end space-x-4">
                <button type="submit" name="salvar" 
                        class="px-4 py-2 bg-[#072a3a] text-white rounded-md hover:bg-[#0a3e56] focus:outline-none focus:ring-2 focus:ring-[#072a3a] focus:ring-opacity-50">
                    Salvar
                </button>
            </div>
        </form>

        <!-- Tabela de Estagiários -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Estagiários Cadastrados</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Setor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Início</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Término</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Exemplo de linha (substituir por dados do banco) -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">João Silva</td>
                            <td class="px-6 py-4 whitespace-nowrap">TI</td>
                            <td class="px-6 py-4 whitespace-nowrap">01/07/2023</td>
                            <td class="px-6 py-4 whitespace-nowrap">30/06/2024</td>
                            <td class="px-6 py-4 whitespace-nowrap">R$ 800,00</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Ativo
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button class="text-indigo-600 hover:text-indigo-900 mr-2">Editar</button>
                                <button class="text-red-600 hover:text-red-900">Excluir</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
HTML;

require_once './src/includes/template.php';
?>
