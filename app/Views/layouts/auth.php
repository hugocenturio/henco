<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($page_title ?? 'Henco') ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="<?= asset('favicon.ico') ?>">
    <script src="<?= asset('js/theme.js') ?>"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= asset('css/theme.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/responsive.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="h-100">

<div id="preloader" style="display: none;">
    <div class="loader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10"></circle>
        </svg>
    </div>
</div>

<?= $content ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notification = document.querySelector('.alert');
        if (notification) {
            setTimeout(() => {
                const alert = new bootstrap.Alert(notification);
                alert.close();
            }, 5000);
        }
    });
</script>
</body>
</html>
