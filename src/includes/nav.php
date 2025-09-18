<?php
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
$current_module = $_GET['module'] ?? '';

// Define os itens do menu com seus links
$menu_items = [
    ["label" => "Faturas", "href" => "faturas.php?module=$current_module", "id" => "faturas"],
    ["label" => "Unidades", "href" => "unidades.php?module=$current_module", "id" => "unidades"],
    ["label" => "KPIs", "href" => "kpis.php?module=$current_module", "id" => "kpis"],
    ["label" => "Recomendações", "href" => "recomendacoes.php?module=$current_module", "id" => "recomendacoes"],
    ["label" => "Documentos", "href" => "documentos.php?module=$current_module", "id" => "documentos"],
    ["label" => "Configurações", "href" => "configuracoes.php?module=$current_module", "id" => "configuracoes"],
];

// Mapeamento dos módulos
$modulos = [
    'agua' => ['label' => 'Água Predial', 'href' => 'agua.php', 'id' => 'agua'],
    'energia' => ['label' => 'Energia Elétrica', 'href' => 'energia.php', 'id' => 'energia'],
    'semparar' => ['label' => 'Sem Parar', 'href' => 'semparar.php', 'id' => 'semparar'],
    'telefone' => ['label' => 'Telefonia Fixa', 'href' => 'telefone.php', 'id' => 'telefone'],
];

// Adiciona o link do módulo atual no início do menu se um módulo estiver selecionado
if (!empty($current_module) && isset($modulos[$current_module])) {
    array_unshift($menu_items, $modulos[$current_module]);
}

// Oculta o menu em páginas específicas como dashboard, suporte e cadastro de faturas.
// Mostra o menu para páginas de módulo (ex: index.php?page=agua) e páginas internas (ex: documentos.php).
$isModulePage = ($current_page === 'index' && !empty($current_module) && $current_module !== 'dashboard');
$isInternalPage = !in_array($current_page, ['index', 'support', 'cad_fatura_pdf']);
if ($isModulePage || $isInternalPage):
?>
<nav class="bg-[#147cac] text-white shadow">
    <div class="mx-auto py-1">
        <ul class="flex space-x-8 justify-center">
            <?php foreach ($menu_items as $item):
                $is_active = ($item['id'] === $current_page);
            ?>
            <li>
                <a href="<?= htmlspecialchars($item['href']) ?>"
                   class="font-semibold transition px-3 py-2 rounded <?php echo $is_active ? 'bg-white text-[#147cac] pointer-events-none border-b-4 border-[#147cac] shadow-lg' : 'text-white hover:bg-[#106191]'; ?>">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>
<?php endif; ?>