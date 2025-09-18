<?php
require_once __DIR__ . '/src/controllers/UnidadesController.php';
require_once __DIR__ . '/src/includes/helpers.php'; // Para consistência e futuras funções
require_once __DIR__ . '/src/includes/header.php';

// Obtém o módulo da URL, se existir.
$module = $_GET['module'] ?? null;

$controller = new UnidadesController();

// Lógica para exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    $id = $_POST['id'];
    if ($controller->destroy($id)) {
        flashMessage('success', 'Unidade excluída com sucesso.');
    } else {
        flashMessage('error', 'Erro ao excluir a unidade.');
    }
    // Redireciona para a mesma página para remover os dados POST e atualizar a lista
    header("Location: unidades.php?module=" . urlencode($module ?? ''));
    exit;
}

// Passa o módulo para o controlador para que ele possa filtrar os dados.
$unidades = $controller->index($module);

$pageTitle = 'Unidades';
ob_start();
?>
<div class="container mx-auto">
    <div class="flex justify-end mb-4">
        <a href="unidade_form.php?module=<?= htmlspecialchars($module ?? '') ?>" class="bg-[#147cac] hover:bg-[#106191] text-white font-bold py-2 px-4 rounded">
            + Nova Unidade
        </a>
    </div>

    <?php if (empty($unidades)): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
            <p>Nenhuma unidade encontrada.</p>
        </div>
    <?php else: ?>
        <table class="min-w-full bg-white border shadow-md rounded-lg">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border-b text-left">Nome</th>
                    <th class="py-2 px-4 border-b text-left">Endereço</th>
                    <th class="py-2 px-4 border-b text-left">Responsável</th>
                    <th class="py-2 px-4 border-b text-left">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unidades as $unidade): ?>
                <tr>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($unidade['nome']); ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($unidade['endereco']); ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($unidade['responsavel']); ?></td>
                    <td class="py-2 px-4 border-b">
                        <a href="unidade_form.php?id=<?= $unidade['id'] ?>&module=<?= htmlspecialchars($module ?? '') ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                        <button onclick="excluirRegistro(<?= $unidade['id'] ?>)" class="text-red-600 hover:text-red-900">Excluir</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function excluirRegistro(id) {
    if (confirm('Tem certeza que deseja excluir este registro? Esta ação não pode ser desfeita.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'unidades.php?module=<?= htmlspecialchars($module ?? '') ?>'; // Envia para a própria página

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        form.appendChild(idInput);

        const excluirInput = document.createElement('input');
        excluirInput.type = 'hidden';
        excluirInput.name = 'excluir';
        excluirInput.value = '1';
        form.appendChild(excluirInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/src/includes/template.php';
