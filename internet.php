<?php
require_once __DIR__ . '/src/includes/auth.php';
$_GET['module'] = 'internet'; // Define o módulo atual

require_once './src/includes/header.php';
require_once './src/models/Internet.php';
$internetModel = new Internet();
$pageTitle = 'Contas de Internet Predial';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['salvar'])) {
        $data = [
            'mes' => $_POST['mes'] ?? null,
            'local' => $_POST['local'] ?? null,
            'provedor' => $_POST['provedor'] ?? null,
            'velocidade' => $_POST['velocidade'] ?? null,
            'valor' => $_POST['valor'] ?? 0,
            'data_vencimento' => $_POST['data_vencimento'] ?? null,
            'Conta_status' => $_POST['status'] ?? 'pendente',
            'secretaria' => $_POST['secretaria'] ?? null,
            'instalacao' => $_POST['instalacao'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'criado_por' => $_SESSION['usuario_id']
        ];

        if ($internetModel->create($data)) {
            $success = "Registro criado com sucesso!";
        } else {
            $error = "Erro ao criar registro.";
        }
    }

    if (isset($_POST['editar']) && isset($_POST['id'])) {
        $data = [
            'mes' => $_POST['mes'] ?? null,
            'local' => $_POST['local'] ?? null,
            'provedor' => $_POST['provedor'] ?? null,
            'velocidade' => $_POST['velocidade'] ?? null,
            'valor' => $_POST['valor'] ?? 0,
            'data_vencimento' => $_POST['data_vencimento'] ?? null,
            'Conta_status' => $_POST['status'] ?? 'pendente',
            'secretaria' => $_POST['secretaria'] ?? null,
            'instalacao' => $_POST['instalacao'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        if ($internetModel->update($_POST['id'], $data)) {
            $success = "Registro atualizado com sucesso!";
        } else {
            $error = "Erro ao atualizar registro.";
        }
    }

    if (isset($_POST['excluir']) && isset($_POST['id'])) {
        if ($internetModel->delete($_POST['id'])) {
            $success = "Registro excluído com sucesso!";
        } else {
            $error = "Erro ao excluir registro.";
        }
    }
}

// Buscar dados para a tabela e cards
$filtros = [];
if (!empty($_GET['filtro_secretaria'])) {
    $filtros['secretaria'] = $_GET['filtro_secretaria'];
}
if (!empty($_GET['filtro_provedor'])) {
    $filtros['provedor'] = $_GET['filtro_provedor'];
}
if (!empty($_GET['filtro_instalacao'])) {
    $filtros['instalacao'] = $_GET['filtro_instalacao'];
}
$registros = $internetModel->buscarComFiltros($filtros);
$stats = $internetModel->getStats();
$totalPendente = $stats['totalPendente'];
$mediaVelocidade = $stats['mediaVelocidade'] ?? 0; // Garante que a variável exista
$valorMensal = $stats['valorMensal'];

ob_start();
?>
    <div class="space-y-6">
    <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500 border-b-2 border-gray-300">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Total Pendente</h3>
            <p class="text-2xl font-bold text-blue-600">R$ <?php echo number_format($totalPendente ?? 0, 2, ',', '.'); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500 border-b-2 border-gray-300">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Consumo Médio</h3>
            <p class="text-2xl font-bold text-green-600"><?php echo number_format($mediaVelocidade, 0, ',', '.'); ?> Mbps</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-purple-500 border-b-2 border-gray-300">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Últimos 6 Meses</h3>
            <div id="valorChart" class="h-20"></div>
        </div>
    </div>
        <form action="" method="POST" class="space-y-4 bg-white p-6 rounded-lg shadow-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="mes" class="block text-sm font-medium text-gray-700">Mês/Ano</label>
                    <input type="month" name="mes" id="mes"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="local" class="block text-sm font-medium text-gray-700">Local</label>
                    <input type="text" name="local" id="local"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="provedor" class="block text-sm font-medium text-gray-700">Provedor</label>
                    <input type="text" name="provedor" id="provedor"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="velocidade" class="block text-sm font-medium text-gray-700">Velocidade</label>
                    <input type="text" name="velocidade" id="velocidade"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="valor" class="block text-sm font-medium text-gray-700">Valor</label>
                    <input type="number" name="valor" id="valor" step="0.01" required
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="data_vencimento" class="block text-sm font-medium text-gray-700">Data de Vencimento</label>
                    <input type="date" name="data_vencimento" id="data_vencimento" required
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="secretaria" class="block text-sm font-medium text-gray-700">Secretaria</label>
                    <input type="text" name="secretaria" id="secretaria"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="instalacao" class="block text-sm font-medium text-gray-700">Instalação</label>
                    <input type="text" name="instalacao" id="instalacao"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                        <option value="">Selecione...</option>
                        <option value="pendente">Pendente</option>
                        <option value="pago">Pago</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="observacoes" class="block text-sm font-medium text-gray-700">Observações</label>
                    <textarea name="observacoes" id="observacoes" rows="1" class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50"></textarea>
                </div>
            </div>
            
            <div class="flex items-center justify-end space-x-2">
                <button type="submit" name="salvar" class="bg-[#072a3a] hover:bg-[#051e2b] text-white font-bold py-2 px-4 rounded-md shadow-sm">
                    Salvar
                </button>
            </div>
        </form>

        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Registros de Internet</h2>

            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label for="filtro_secretaria" class="block text-sm font-medium text-gray-700">Secretaria</label>
                    <input type="text" name="filtro_secretaria" id="filtro_secretaria" value="<?= htmlspecialchars($_GET['filtro_secretaria'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="filtro_provedor" class="block text-sm font-medium text-gray-700">Provedor</label>
                    <input type="text" name="filtro_provedor" id="filtro_provedor" value="<?= htmlspecialchars($_GET['filtro_provedor'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="filtro_instalacao" class="block text-sm font-medium text-gray-700">Instalação</label>
                    <input type="text" name="filtro_instalacao" id="filtro_instalacao" value="<?= htmlspecialchars($_GET['filtro_instalacao'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md shadow-sm">
                        Filtrar
                    </button>
                    <a href="internet.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md shadow-sm ml-2">
                        Limpar
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mês/Ano</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Velocidade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Secretaria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instalação</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observações</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($registros as $registro): ?>
                            <tr id="row-<?= htmlspecialchars($registro['id_internet']) ?>">
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($registro['mes'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($registro['local'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($registro['provedor'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($registro['velocidade'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">R$ <?= htmlspecialchars(number_format($registro['valor'] ?? 0, 2, ',', '.')); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars(date('d/m/Y', strtotime($registro['data_vencimento'] ?? ''))); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($registro['secretaria'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($registro['instalacao'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($registro['observacoes'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (($registro['Conta_status'] ?? '') === 'pago'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Pago</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editarRegistro(<?= $registro['id_internet'] ?>)" class="text-indigo-600 hover:text-indigo-900 mr-2">Editar</button>
                                    <button onclick="excluirRegistro(<?= $registro['id_internet'] ?>)" class="text-red-600 hover:text-red-900">Excluir</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
$valorMensalJSON = json_encode($valorMensal ?? []);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const valorData = <?= $valorMensalJSON ?>;
    if (document.getElementById('valorChart') && valorData.length > 0) {
        const labels = valorData.map(item => item.mes_ano);
        const dados = valorData.map(item => item.valor_total);

        const ctx = document.getElementById('valorChart').getContext('2d');
        const valorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Valor (R$)',
                    data: dados,
                    backgroundColor: 'rgba(167, 139, 250, 0.2)',
                    borderColor: 'rgb(167, 139, 250)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Funções para edição e exclusão
    function editarRegistro(id) {
        if (confirm('Deseja editar este registro?')) {
            // A lógica de carregamento dos dados no formulário precisaria ser implementada.
            // Por simplicidade, esta função ainda não preenche o formulário.
            alert('Funcionalidade de edição a ser implementada: carregar dados no formulário.');
        }
    }

    function excluirRegistro(id) {
        if (confirm('Tem certeza que deseja excluir este registro?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'internet.php';
            form.innerHTML = `
                <input type="hidden" name="id" value="${id}">
                <input type="hidden" name="excluir" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
<?php
$content = ob_get_clean();
require_once './src/includes/template.php';
?>