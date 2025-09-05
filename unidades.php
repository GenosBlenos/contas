<?php
require_once __DIR__ . '/src/controllers/UnidadesController.php';
require_once __DIR__ . '/src/includes/header.php';

// Obtém o módulo da URL, se existir.
$module = $_GET['module'] ?? null;

$controller = new UnidadesController();
// Passa o módulo para o controlador para que ele possa filtrar os dados.
$unidades = $controller->index($module);

$pageTitle = 'Unidades';
ob_start();
?>
<div class="container mx-auto">
    <table class="min-w-full bg-white border">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b">Nome</th>
                <th class="py-2 px-4 border-b">Endereço</th>
                <th class="py-2 px-4 border-b">Responsável</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($unidades as $unidade): ?>
            <tr>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($unidade['nome']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($unidade['endereco']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($unidade['responsavel']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/src/includes/template.php';

