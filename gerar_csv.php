<?php

include 'contas.php';

$contas = getContasFromDatabase();
gerarCSVContasPendentes($contas);
exit;
?>