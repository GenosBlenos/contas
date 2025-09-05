<?php
require_once __DIR__ . '/src/controllers/DocumentosController.php';
require_once __DIR__ . '/src/includes/header.php';

// Obtém o módulo da URL, se existir.
$module = $_GET['module'] ?? null;

$controller = new DocumentosController();
// Passa o módulo para o controlador para que ele possa filtrar os dados.
$documentos = $controller->index($module);

$pageTitle = 'Documentos';
ob_start();
?>
    <table class="min-w-full bg-white border">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b">Titulo</th>
                <th class="py-2 px-4 border-b">Arquivo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documentos as $documento): ?>
            <tr>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($documento['Titulo']); ?></td>
                <td class="py-2 px-4 border-b">R$ <?php echo htmlspecialchars($documento['Arquivo']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php
$content = ob_get_clean();
require __DIR__ . '/src/includes/template.php';