<?php

namespace App\Controllers;

use App\Core\Controller;

class SettingsController extends Controller
{
    private const KEYS = ['manager_email', 'send_email', 'currency', 'locale', 'company_name'];

    public function index(): void
    {
        $this->requireAdmin();
        $db = $this->db();

        if ($this->request->isPost()) {
            csrf_verify();
            foreach (self::KEYS as $k) {
                $value = trim($_POST[$k] ?? '');
                if ($value !== '') {
                    if (in_array($k, ['manager_email', 'send_email'], true) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->flash('error', "$k must be a valid email.");
                        continue;
                    }
                    $this->upsert($k, $value);
                    if ($k === 'locale')       $_SESSION['locale']       = $value;
                    if ($k === 'company_name') $_SESSION['company_name'] = $value;
                }
            }
            $this->flash('success', 'Settings updated.');
            $this->redirect('/settings');
        }

        $values = [];
        foreach (self::KEYS as $k) {
            $values[$k] = $this->fetch($k);
        }
        if (empty($values['currency'])) $values['currency'] = '€';

        $this->view('settings/index', ['values' => $values], 'main', [
            'page_title' => 'Settings', 'current' => 'settings',
        ]);
    }

    public function api(): void
    {
        $payload = [];
        foreach (self::KEYS as $k) {
            $payload[$k] = $this->fetch($k);
        }
        $this->json($payload);
    }

    private function fetch(string $key): string
    {
        $stmt = $this->db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->bind_param('s', $key); $stmt->execute();
        $stmt->bind_result($v); $stmt->fetch(); $stmt->close();
        return (string) ($v ?? '');
    }

    private function upsert(string $key, string $value): void
    {
        $db = $this->db();
        $stmt = $db->prepare('SELECT id FROM settings WHERE setting_key = ?');
        $stmt->bind_param('s', $key); $stmt->execute(); $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $u = $db->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
            $u->bind_param('ss', $value, $key); $u->execute(); $u->close();
        } else {
            $stmt->close();
            $i = $db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
            $i->bind_param('ss', $key, $value); $i->execute(); $i->close();
        }
    }
}
