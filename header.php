<?php
// Start the session with secure settings before any output
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// Connect to the database
include 'dbconnect.php';

$page_title = "Henco";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_reorder'])) {
    unset($_SESSION['reorder_order_id']);
    // Opcionalmente podes redirecionar para a mesma página 
    // para evitar re-submissão do formulário ao dar refresh
    header('Location: '.$_SERVER['REQUEST_URI']);
    exit();
}

?>