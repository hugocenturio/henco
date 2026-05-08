<?php /** @var array $errors */ /** @var bool $success */ /** @var array $input */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Henco — Installation Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f0f2f5; }
        .setup-card { max-width:700px; margin:3rem auto; }
        .section-title {
            font-size:.72rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.08em; color:#6c757d; margin:1.75rem 0 .5rem;
            border-bottom:1px solid #dee2e6; padding-bottom:.25rem;
        }
    </style>
</head>
<body>
<div class="setup-card px-3">
    <div class="card shadow-sm">
        <div class="card-body p-4">

            <?php if ($success): ?>
                <div class="text-center py-4">
                    <h2 class="text-success mb-3">&#10003; Installation Complete</h2>
                    <p>The database has been set up and the admin account has been created.</p>
                    <p class="text-danger fw-semibold mt-3">For security, restrict access to <code>/setup</code>.</p>
                    <a href="<?= url('/login') ?>" class="btn btn-primary mt-2">Go to Login &rarr;</a>
                </div>
            <?php else: ?>
                <h2 class="mb-0">Henco &mdash; Installation Wizard</h2>
                <p class="text-muted small mb-3">Fill in all sections and submit once to install.</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('/setup') ?>" autocomplete="off" novalidate>
                    <?= csrf_field() ?>

                    <p class="section-title">Database</p>
                    <div class="mb-3">
                        <label class="form-label">Host</label>
                        <input type="text" name="db_host" class="form-control" required
                               value="<?= e($input['db_host'] ?? 'localhost') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Database Name</label>
                        <input type="text" name="db_name" class="form-control" required value="<?= e($input['db_name'] ?? '') ?>">
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="db_user" class="form-control" required value="<?= e($input['db_user'] ?? '') ?>">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="db_password" class="form-control" autocomplete="new-password">
                        </div>
                    </div>

                    <p class="section-title">Mailjet Email API <span class="text-muted fw-normal" style="text-transform:none;font-size:.8rem;">— optional</span></p>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">API Key</label>
                            <input type="text" name="mj_key" class="form-control" value="<?= e($input['mj_key'] ?? '') ?>">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">API Secret</label>
                            <input type="password" name="mj_secret" class="form-control" autocomplete="new-password">
                        </div>
                    </div>

                    <p class="section-title">Application Settings</p>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" required value="<?= e($input['company_name'] ?? '') ?>">
                        </div>
                        <div class="col-3 mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control" maxlength="5" required value="<?= e($input['currency'] ?? '€') ?>">
                        </div>
                        <div class="col-3 mb-3">
                            <label class="form-label">Locale</label>
                            <select name="locale" class="form-select">
                                <option value="pt" <?= ($input['locale'] ?? 'pt') === 'pt' ? 'selected' : '' ?>>pt</option>
                                <option value="en" <?= ($input['locale'] ?? '') === 'en' ? 'selected' : '' ?>>en</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Manager Email</label>
                            <input type="email" name="manager_email" class="form-control" required value="<?= e($input['manager_email'] ?? '') ?>">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Sender Email</label>
                            <input type="email" name="send_email" class="form-control" required value="<?= e($input['send_email'] ?? '') ?>">
                        </div>
                    </div>

                    <p class="section-title">Admin Account</p>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="admin_username" class="form-control" required value="<?= e($input['admin_username'] ?? '') ?>">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="admin_email" class="form-control" required value="<?= e($input['admin_email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password <small class="text-muted">(min 8 chars)</small></label>
                        <input type="password" name="admin_password" class="form-control" autocomplete="new-password" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">Install Henco</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
