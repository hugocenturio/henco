<?php
// logout.php

require_once __DIR__ . '/config/security.php';

// Clear all session variables
$_SESSION = array();

// Drop the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Redireciona para a página de login com a mensagem de sucesso
header('Location: index.php?logout=success');
exit();
?>
