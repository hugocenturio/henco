<?php

namespace App\Controllers;

use App\Core\Controller;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS  = 5;
    private const LOCKOUT_SECS  = 15 * 60;

    public function login(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $_SESSION['login_attempts']     = $_SESSION['login_attempts']     ?? 0;
        $_SESSION['login_locked_until'] = $_SESSION['login_locked_until'] ?? 0;

        $message = '';
        $messageClass = '';

        if ($this->request->isPost()) {
            csrf_verify();

            if ($_SESSION['login_locked_until'] > time()) {
                $remaining = (int) ceil(($_SESSION['login_locked_until'] - time()) / 60);
                $message = "Too many failed attempts. Try again in {$remaining} minute(s).";
                $messageClass = 'error';
            } else {
                $email    = trim($this->request->input('email', ''));
                $password = $this->request->input('password', '');
                if ($this->attemptLogin($email, $password)) {
                    $this->redirect('/dashboard');
                }
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= self::MAX_ATTEMPTS) {
                    $_SESSION['login_locked_until'] = time() + self::LOCKOUT_SECS;
                    $_SESSION['login_attempts']     = 0;
                    $message = 'Too many failed attempts. Please wait 15 minutes before trying again.';
                } else {
                    $message = 'Incorrect email or password.';
                }
                $messageClass = 'error';
            }
        }

        $this->view('auth/login', compact('message', 'messageClass'), 'auth', ['page_title' => 'Login - Henco']);
    }

    private function attemptLogin(string $email, string $password): bool
    {
        $stmt = $this->db()->prepare(
            'SELECT user_id, username, password, is_active, role_id FROM users WHERE email = ?'
        );
        if (!$stmt) return false;
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows !== 1) {
            $stmt->close();
            return false;
        }
        $stmt->bind_result($id, $username, $hash, $active, $roleId);
        $stmt->fetch();
        $stmt->close();

        if (!$active || !password_verify($password, $hash)) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']            = $id;
        $_SESSION['username']           = $username;
        $_SESSION['role_id']            = $roleId;
        $_SESSION['is_admin']           = ((int) $roleId === 1);
        $_SESSION['login_attempts']     = 0;
        $_SESSION['login_locked_until'] = 0;
        unset($_SESSION['csrf_token']);
        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        $this->redirect('/login?logout=success');
    }

    public function activate(): void
    {
        $code = trim($this->request->input('code', ''));
        $message = '';
        $messageClass = '';

        if ($code === '') {
            $message = 'Activation code not provided.';
            $messageClass = 'error';
        } else {
            $db = $this->db();
            $stmt = $db->prepare('SELECT user_id FROM users WHERE activation_code = ? AND is_active = 0');
            $stmt->bind_param('s', $code);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                $update = $db->prepare('UPDATE users SET is_active = 1, activation_code = NULL WHERE activation_code = ?');
                $update->bind_param('s', $code);
                if ($update->execute()) {
                    $message = 'Your account has been successfully activated! You can now log in.';
                    $messageClass = 'success';
                } else {
                    $message = 'Error activating the account. Please try again.';
                    $messageClass = 'error';
                }
                $update->close();
            } else {
                $message = 'Invalid activation code or account already activated.';
                $messageClass = 'error';
            }
            $stmt->close();
        }

        $this->view('auth/activate', compact('message', 'messageClass'), 'auth', ['page_title' => 'Account activation - Henco']);
    }
}
