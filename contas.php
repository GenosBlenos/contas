<?php

function getContasFromDatabase() {
    // Simulação de dados do banco de dados.
    // Substitua isso pela sua lógica real de acesso ao banco de dados.
    return [
        [
            'modulo' => 'agua',
            'descricao' => 'Conta de água de janeiro',
            'valor' => 150.00,
            'status' => 'pendente'
        ],
        [
            'modulo' => 'energia',
            'descricao' => 'Conta de energia de fevereiro',
            'valor' => 250.00,
            'status' => 'pago'
        ],
        [
            'modulo' => 'telefone',
            'descricao' => 'Conta de telefone de março',
            'valor' => 80.00,
            'status' => 'pendente'
        ],
        [
            'modulo' => 'semparar',
            'descricao' => 'Mensalidade Sem Parar de abril',
            'valor' => 50.00,
            'status' => 'pendente'
        ],
        [
            'modulo' => 'agua',
            'descricao' => 'Conta de água de fevereiro',
            'valor' => 160.00,
            'status' => 'pago'
        ],
    ];
}

function gerarCSVContasPendentes($contas, $nomeArquivo = 'contas_pendentes.csv') {
    // Filtrar contas pendentes
    $contasPendentes = array_filter($contas, function($conta) {
        return $conta['status'] === 'pendente';
    });

    // Agrupar por módulo
    $contasPorModulo = [];
    foreach ($contasPendentes as $conta) {
        $modulo = $conta['modulo'];
        if (!isset($contasPorModulo[$modulo])) {
            $contasPorModulo[$modulo] = [];
        }
        $contasPorModulo[$modulo][] = $conta;
    }

    // Criar o arquivo CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');

    $output = fopen('php://output', 'w');

    // Cabeçalho do CSV
    fputcsv($output, ['Módulo', 'Descrição', 'Valor', 'Status']);

    // Dados do CSV
    foreach ($contasPorModulo as $modulo => $contasDoModulo) {
        foreach ($contasDoModulo as $conta) {
            fputcsv($output, [$modulo, $conta['descricao'], $conta['valor'], $conta['status']]);
        }
    }

    fclose($output);
}
?>