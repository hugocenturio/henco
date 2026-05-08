<?php
require_once __DIR__ . '/config/security.php';

// Connect to the database
include 'dbconnect.php';

$page_title = "Henco";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_reorder'])) {
    csrf_verify();
    unset($_SESSION['reorder_order_id']);
    // Redirect to avoid form re-submission on refresh
    header('Location: '.$_SERVER['REQUEST_URI']);
    exit();
}

?>