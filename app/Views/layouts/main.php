<?php
/** @var string $content */
$locale = $_SESSION['locale'] ?? 'en';
$locale = preg_match('/^[a-z]{2}(?:-[A-Z]{2})?$/', $locale) ? $locale : 'en';
$pageTitle = ($_SESSION['company_name'] ?? 'Henco') . ' | ' . ($page_title ?? 'Henco');
$current   = $current ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="base-url" content="<?= e(url('/')) ?>">
    <meta name="theme-color" content="#7571f9">
    <title><?= e($pageTitle) ?></title>

    <script src="<?= asset('js/theme.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>const locale = <?= json_encode($locale) ?>;</script>
    <script src="<?= asset('js/locales.js') ?>"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href="<?= asset('css/theme.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/responsive.css') ?>" rel="stylesheet">
</head>
<body data-locale="<?= e($locale) ?>">
<?php include __DIR__ . '/../partials/flash.php'; ?>

<div id="preloader">
    <div class="loader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10"/>
        </svg>
    </div>
</div>

<div id="main-wrapper">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <?php include __DIR__ . '/../partials/topbar.php'; ?>

    <div class="content-body" style="min-height:1100px;">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <?= $content ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer bg-light text-center text-dark py-3 shadow fixed-bottom">
        <div class="container">
            <span>&copy; <?= date('Y') ?> Henco. All rights reserved.</span>
        </div>
    </footer>
</div>

<?php include __DIR__ . '/../partials/bottom_nav.php'; ?>

<script>
(function () {
    const hamburger = document.querySelector('.nav-control .hamburger');
    if (hamburger) {
        hamburger.addEventListener('click', function (e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-open');
        });
    }
    document.addEventListener('click', function (e) {
        if (!document.body.classList.contains('sidebar-open')) return;
        const sidebar = document.querySelector('.nk-sidebar');
        if (sidebar && !sidebar.contains(e.target) && !e.target.closest('.nav-control .hamburger')) {
            document.body.classList.remove('sidebar-open');
        }
    });
})();
</script>

<script src="<?= asset('plugins/common/common.min.js') ?>"></script>
<script src="<?= asset('js/custom.min.js') ?>"></script>
<script src="<?= asset('js/settings.js') ?>"></script>
<script src="<?= asset('js/gleek.js') ?>"></script>
<script src="<?= asset('js/changecurrency.js') ?>"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="<?= asset('js/translator.js') ?>"></script>
<script src="<?= asset('js/pages.js') ?>"></script>
<script src="<?= asset('js/datatables.js') ?>"></script>
<script src="<?= asset('js/loader.js') ?>"></script>
</body>
</html>
