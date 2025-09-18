<?php
require_once __DIR__ . '/src/controllers/RecomendacoesController.php';
require_once __DIR__ . '/src/includes/helpers.php'; // Para consistência e futuras funções
require_once __DIR__ . '/src/includes/header.php';

// Obtém o módulo da URL, se existir.
$module = $_GET['module'] ?? null;

$controller = new RecomendacoesController();
// Passa o módulo para o controlador para que ele possa filtrar os dados.
$recomendacoes = $controller->index($module);

$pageTitle = 'Recomendações';
ob_start();
?>
<div class="container mx-auto">
    <?php if (empty($recomendacoes)): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
            <p>Nenhuma recomendação encontrada.</p>
        </div>
    <?php else: ?>
        <table class="min-w-full bg-white border">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b text-left">Título</th>
                    <th class="py-2 px-4 border-b text-left">Descrição</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recomendacoes as $rec): ?>
                <tr>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($rec['titulo']); ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($rec['descricao']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/src/includes/template.php';
