<!DOCTYPE html>
<html><head>
    <title>Henco — Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body>
<div class="container py-5" style="max-width:600px;">
    <div class="alert alert-warning">
        <h4>Setup is already complete.</h4>
        <p>Restrict access to <code>/setup</code>. To re-install, remove the <code>.env</code> file (or <code>config/config.php</code>) on the server and reload this page.</p>
        <a href="<?= url('/login') ?>" class="btn btn-primary mt-2">Go to Login &rarr;</a>
    </div>
</div>
</body></html>
