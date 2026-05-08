<?php

namespace App\Controllers;

use App\Core\Controller;
use mysqli;

class SetupController extends Controller
{
    public function index(): void
    {
        $configPath  = dirname(__DIR__, 2) . '/config/config.php';
        $envPath     = dirname(__DIR__, 2) . '/.env';
        $alreadyDone = (is_file($configPath) && !str_contains((string) file_get_contents($configPath), "''"))
                    || (is_file($envPath));

        if ($alreadyDone) {
            $this->view('setup/already_done', [], null);
            return;
        }

        $errors  = [];
        $success = false;
        $input   = [];

        if ($this->request->isPost()) {
            csrf_verify();
            $input = $_POST;

            $errors = $this->validate($input);
            if (empty($errors)) {
                $errors = $this->install($input);
                if (empty($errors)) {
                    $success = true;
                }
            }
        }

        $this->view('setup/index', compact('errors', 'success', 'input'), null);
    }

    private function validate(array $i): array
    {
        $errors = [];
        if (empty($i['db_host']))      $errors[] = 'Database host is required.';
        if (empty($i['db_name']))      $errors[] = 'Database name is required.';
        if (empty($i['db_user']))      $errors[] = 'Database user is required.';
        if (empty($i['company_name'])) $errors[] = 'Company name is required.';
        if (!filter_var($i['manager_email'] ?? '', FILTER_VALIDATE_EMAIL))
            $errors[] = 'Manager email must be valid.';
        if (!filter_var($i['send_email'] ?? '', FILTER_VALIDATE_EMAIL))
            $errors[] = 'Sender email must be valid.';
        if (empty($i['admin_username']))
            $errors[] = 'Admin username is required.';
        if (!filter_var($i['admin_email'] ?? '', FILTER_VALIDATE_EMAIL))
            $errors[] = 'Admin email must be valid.';
        if (strlen($i['admin_password'] ?? '') < 8)
            $errors[] = 'Admin password must be at least 8 characters.';
        return $errors;
    }

    private function install(array $i): array
    {
        $errors = [];
        $db = @new mysqli($i['db_host'], $i['db_user'], $i['db_password'] ?? '', $i['db_name']);
        if ($db->connect_errno) {
            return ['Could not connect to the database: ' . $db->connect_error];
        }
        $db->set_charset('utf8mb4');

        $schemaFile = dirname(__DIR__, 2) . '/database/schema.sql';
        if (!is_file($schemaFile)) {
            return ['database/schema.sql not found.'];
        }
        $sql = (string) file_get_contents($schemaFile);
        if ($db->multi_query($sql)) {
            do {
                if ($res = $db->use_result()) { $res->free(); }
            } while ($db->more_results() && $db->next_result());
        } else {
            return ['Schema error: ' . $db->error];
        }

        $settings = [
            'company_name'  => $i['company_name'],
            'currency'      => $i['currency']      ?? '€',
            'locale'        => $i['locale']        ?? 'pt',
            'manager_email' => $i['manager_email'],
            'send_email'    => $i['send_email'],
        ];
        foreach ($settings as $k => $v) {
            $st = $db->prepare(
                'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            );
            $st->bind_param('ss', $k, $v);
            $st->execute();
            $st->close();
        }

        $hashed = password_hash($i['admin_password'], PASSWORD_BCRYPT);
        $chk = $db->prepare('SELECT user_id FROM users WHERE email = ?');
        $chk->bind_param('s', $i['admin_email']);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows === 0) {
            $ins = $db->prepare(
                'INSERT INTO users (username, email, password, role_id, is_active) VALUES (?, ?, ?, 1, 1)'
            );
            $ins->bind_param('sss', $i['admin_username'], $i['admin_email'], $hashed);
            if (!$ins->execute()) {
                $errors[] = 'Failed to create admin user: ' . $ins->error;
            }
            $ins->close();
        }
        $chk->close();
        $db->close();

        if (empty($errors)) {
            $envContent = "APP_ENV=production\nAPP_DEBUG=false\n\n"
                . 'DB_HOST=' . $i['db_host'] . "\n"
                . 'DB_NAME=' . $i['db_name'] . "\n"
                . 'DB_USER=' . $i['db_user'] . "\n"
                . 'DB_PASSWORD=' . ($i['db_password'] ?? '') . "\n\n"
                . 'MAILJET_API_KEY=' . ($i['mj_key'] ?? '') . "\n"
                . 'MAILJET_API_SECRET=' . ($i['mj_secret'] ?? '') . "\n";

            $envPath = dirname(__DIR__, 2) . '/.env';
            file_put_contents($envPath, $envContent);
            chmod($envPath, 0640);
        }
        return $errors;
    }
}
