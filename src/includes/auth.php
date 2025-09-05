<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redireciona para login se não estiver logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: /login.php');
    exit;
}
