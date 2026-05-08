<?php
require_once __DIR__ . '/config/security.php';
include 'get_settings.php';

$company_name = $_SESSION['Henco'] ?? 'Default Company';
$locale = $_SESSION['locale'] ?? get_setting($mysqli, 'locale') ?? 'en';

if (!isset($_SESSION['locale'])) {
    $_SESSION['locale'] = $locale;
}

// Whitelist locale for safe injection into HTML/JS (defence in depth).
$locale = preg_match('/^[a-z]{2}(?:-[A-Z]{2})?$/', $locale) ? $locale : 'en';

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
        
    <!-- Bootstrap JavaScript -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>        
    
    <!-- Custom JavaScript -->    
    <script> const locale = <?php echo json_encode($locale); ?>;</script>
    <script src="js/locales.js"></script>  

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">        
        
    <!-- Bootstrap CSS -->   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
    
    <!-- Fontawesome CSS --> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />    
	

           
    <!-- Custom Stylesheet -->        
    <link href="css/style.css" rel="stylesheet">
    
 </head>

<body data-locale="<?php echo htmlspecialchars($locale, ENT_QUOTES, 'UTF-8'); ?>">
<?php include 'flash_messages.php';  ?>      
    <!-- Preloader -->
    <div id="preloader">
        <div class="loader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
            </svg>
        </div>
    </div>

    <!-- Main Wrapper -->
    <div id="main-wrapper">

        <!-- Nav Header -->
        <div class="nav-header">
            <div class="brand-logo">
                <a href="index.html">
                    <b class="logo-abbr"><img src="images/logo.png" alt=""> </b>
                    <span class="logo-compact"><img src="./images/logo-compact.png" alt=""></span>
                    <span class="brand-title"><img src="images/logo-text.png" alt=""></span>
                </a>
            </div>
        </div>

        <!-- Sidebar -->
        <?php include 'menu.php'; ?>    


        <!-- Content Body -->
        <div class="">
            <div class="container-fluid">
                <!-- Add your content here -->
