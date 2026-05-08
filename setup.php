<?php
/**
 * Henco Installation Wizard
 *
 * Run this script ONCE on a fresh server to:
 *   1. Write config/config.php (DB + Mailjet credentials)
 *   2. Create all database tables (from database/schema.sql)
 *   3. Insert default settings
 *   4. Create the first admin user
 *
 * DELETE this file after setup is complete.
 */

// Abort if already fully installed (config exists and has real values)
if (file_exists(__DIR__ . '/config/config.php')) {
    $cfg = file_get_contents(__DIR__ . '/config/config.php');
    if (strpos($cfg, "define('DB_HOST'") !== false && strpos($cfg, "''") === false) {
        die('<p style="font-family:sans-serif;color:red;padding:2rem;">
            Setup has already been completed.<br>
            Delete <code>setup.php</code> from the server for security.<br>
            If you need to reconfigure, remove <code>config/config.php</code> first.
        </p>');
    }
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Collect & validate input
    $db_host        = trim($_POST['db_host']        ?? '');
    $db_name        = trim($_POST['db_name']        ?? '');
    $db_user        = trim($_POST['db_user']        ?? '');
    $db_password    = $_POST['db_password']          ?? '';

    $mj_key         = trim($_POST['mj_key']         ?? '');
    $mj_secret      = trim($_POST['mj_secret']      ?? '');

    $company_name   = trim($_POST['company_name']   ?? '');
    $currency       = trim($_POST['currency']       ?? '€');
    $locale         = trim($_POST['locale']         ?? 'pt');
    $manager_email  = trim($_POST['manager_email']  ?? '');
    $send_email_val = trim($_POST['send_email']      ?? '');

    $admin_username = trim($_POST['admin_username'] ?? '');
    $admin_email    = trim($_POST['admin_email']    ?? '');
    $admin_password = $_POST['admin_password']       ?? '';

    if (empty($db_host))       $errors[] = 'Database host is required.';
    if (empty($db_name))       $errors[] = 'Database name is required.';
    if (empty($db_user))       $errors[] = 'Database user is required.';
    if (empty($company_name))  $errors[] = 'Company name is required.';
    if (!filter_var($manager_email, FILTER_VALIDATE_EMAIL))
                               $errors[] = 'Manager email must be a valid email address.';
    if (!filter_var($send_email_val, FILTER_VALIDATE_EMAIL))
                               $errors[] = 'Sender email must be a valid email address.';
    if (empty($admin_username)) $errors[] = 'Admin username is required.';
    if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL))
                               $errors[] = 'Admin email must be a valid email address.';
    if (strlen($admin_password) < 8)
                               $errors[] = 'Admin password must be at least 8 characters.';

    if (empty($errors)) {

        // 2. Test DB connection
        $test = @new mysqli($db_host, $db_user, $db_password, $db_name);
        if ($test->connect_errno) {
            $errors[] = 'Could not connect to the database: ' . $test->connect_error;
        } else {

            // 3. Run schema SQL
            $schema_file = __DIR__ . '/database/schema.sql';
            if (!file_exists($schema_file)) {
                $errors[] = 'database/schema.sql not found. Cannot create tables.';
            } else {
                $sql = file_get_contents($schema_file);
                if ($test->multi_query($sql)) {
                    do {
                        if ($res = $test->use_result()) {
                            $res->free();
                        }
                    } while ($test->more_results() && $test->next_result());
                } else {
                    $errors[] = 'Schema error: ' . $test->error;
                }
            }

            if (empty($errors)) {

                // 4. Insert/update settings
                $settings = [
                    'company_name'  => $company_name,
                    'currency'      => $currency,
                    'locale'        => $locale,
                    'manager_email' => $manager_email,
                    'send_email'    => $send_email_val,
                ];
                foreach ($settings as $k => $v) {
                    $st = $test->prepare(
                        'INSERT INTO settings (setting_key, setting_value)
                         VALUES (?, ?)
                         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
                    );
                    $st->bind_param('ss', $k, $v);
                    $st->execute();
                    $st->close();
                }

                // 5. Create admin user (skip if email already exists)
                $hashed = password_hash($admin_password, PASSWORD_BCRYPT);
                $chk = $test->prepare('SELECT user_id FROM users WHERE email = ?');
                $chk->bind_param('s', $admin_email);
                $chk->execute();
                $chk->store_result();
                if ($chk->num_rows === 0) {
                    $ins = $test->prepare(
                        'INSERT INTO users (username, email, password, role_id, is_active) VALUES (?, ?, ?, 1, 1)'
                    );
                    $ins->bind_param('sss', $admin_username, $admin_email, $hashed);
                    if (!$ins->execute()) {
                        $errors[] = 'Failed to create admin user: ' . $ins->error;
                    }
                    $ins->close();
                }
                $chk->close();
                $test->close();
            }

            if (empty($errors)) {

                // 6. Write config/config.php
                if (!is_dir(__DIR__ . '/config')) {
                    mkdir(__DIR__ . '/config', 0750, true);
                }

                $config_content = "<?php\n"
                    . "define('DB_HOST',           '" . addslashes($db_host)      . "');\n"
                    . "define('DB_NAME',           '" . addslashes($db_name)      . "');\n"
                    . "define('DB_USER',           '" . addslashes($db_user)      . "');\n"
                    . "define('DB_PASSWORD',       '" . addslashes($db_password)  . "');\n\n"
                    . "// Mailjet API credentials\n"
                    . "define('MAILJET_API_KEY',    '" . addslashes($mj_key)    . "');\n"
                    . "define('MAILJET_API_SECRET', '" . addslashes($mj_secret) . "');\n";

                file_put_contents(__DIR__ . '/config/config.php', $config_content);
                chmod(__DIR__ . '/config/config.php', 0640);

                // 7. Write config/.htaccess
                $htaccess = "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
                          . "<IfModule !mod_authz_core.c>\n    Order deny,allow\n    Deny from all\n</IfModule>\n";
                file_put_contents(__DIR__ . '/config/.htaccess', $htaccess);

                $success = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Henco — Installation Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .setup-card { max-width: 700px; margin: 3rem auto; }
        .section-title {
            font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .08em; color: #6c757d; margin: 1.75rem 0 .5rem;
            border-bottom: 1px solid #dee2e6; padding-bottom: .25rem;
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
                    <p class="text-danger fw-semibold mt-3">
                        For security, <strong>delete <code>setup.php</code></strong> from the server now.
                    </p>
                    <a href="index.php" class="btn btn-primary mt-2">Go to Login &rarr;</a>
                </div>

            <?php else: ?>
                <h2 class="mb-0">Henco &mdash; Installation Wizard</h2>
                <p class="text-muted small mb-3">Fill in all sections and submit once to install.</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $e): ?>
                                <li><?php echo htmlspecialchars($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off" novalidate>

                    <p class="section-title">Database</p>
                    <div class="mb-3">
                        <label class="form-label">Host</label>
                        <input type="text" name="db_host" class="form-control" required
                               value="<?php echo htmlspecialchars($_POST['db_host'] ?? 'localhost'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Database Name</label>
                        <input type="text" name="db_name" class="form-control" required
                               value="<?php echo htmlspecialchars($_POST['db_name'] ?? ''); ?>">
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="db_user" class="form-control" required autocomplete="off"
                                   value="<?php echo htmlspecialchars($_POST['db_user'] ?? ''); ?>">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="db_password" class="form-control" autocomplete="new-password">
                        </div>
                    </div>

                    <p class="section-title">
                        Mailjet Email API
                        <span class="text-muted fw-normal normal-case" style="text-transform:none;font-size:.8rem;">
                            — optional, required for sending order emails
                        </span>
                    </p>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">API Key</label>
                            <input type="text" name="mj_key" class="form-control"
                                   value="<?php echo htmlspecialchars($_POST['mj_key'] ?? ''); ?>">
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
                            <input type="text" name="company_name" class="form-control" required
                                   value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>">
                        </div>
                        <div class="col-3 mb-3">
                            <label class="form-label">Currency Symbol</label>
                            <input type="text" name="currency" class="form-control" maxlength="5" required
                                   value="<?php echo htmlspecialchars($_POST['currency'] ?? '€'); ?>">
                        </div>
                        <div class="col-3 mb-3">
                            <label class="form-label">Locale</label>
                            <select name="locale" class="form-select">
                                <option value="pt" <?php echo ($_POST['locale'] ?? 'pt') === 'pt' ? 'selected' : ''; ?>>pt</option>
                                <option value="en" <?php echo ($_POST['locale'] ?? '') === 'en' ? 'selected' : ''; ?>>en</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Manager Email <small class="text-muted">(receives order notifications)</small></label>
                            <input type="email" name="manager_email" class="form-control" required
                                   value="<?php echo htmlspecialchars($_POST['manager_email'] ?? ''); ?>">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Sender Email <small class="text-muted">(From address)</small></label>
                            <input type="email" name="send_email" class="form-control" required
                                   value="<?php echo htmlspecialchars($_POST['send_email'] ?? ''); ?>">
                        </div>
                    </div>

                    <p class="section-title">Admin Account</p>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="admin_username" class="form-control" required
                                   value="<?php echo htmlspecialchars($_POST['admin_username'] ?? ''); ?>">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="admin_email" class="form-control" required
                                   value="<?php echo htmlspecialchars($_POST['admin_email'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password <small class="text-muted">(minimum 8 characters)</small></label>
                        <input type="password" name="admin_password" class="form-control"
                               autocomplete="new-password" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">Install Henco</button>

                </form>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>
