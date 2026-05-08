<?php

namespace App\Controllers;

use App\Core\Controller;

class ProfileController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];
        $db     = $this->db();

        if ($this->request->isPost()) {
            csrf_verify();

            if (isset($_POST['update_profile'])) {
                $username = trim($_POST['username'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                $stmt = $db->prepare('UPDATE users SET username = ?, email = ? WHERE user_id = ?');
                $stmt->bind_param('ssi', $username, $email, $userId);
                $stmt->execute();
                $stmt->close();
                $_SESSION['username'] = $username;
                $this->flash('success', 'Profile updated.');
                $this->redirect('/profile');
            }

            if (isset($_POST['change_password'])) {
                $current = $_POST['current_password'] ?? '';
                $new     = $_POST['new_password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';

                $stmt = $db->prepare('SELECT password FROM users WHERE user_id = ?');
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $stmt->bind_result($hash);
                $stmt->fetch();
                $stmt->close();

                if (!password_verify($current, $hash)) {
                    $this->flash('error', 'Current password is incorrect.');
                } elseif ($new !== $confirm) {
                    $this->flash('error', 'New password and confirmation do not match.');
                } elseif (strlen($new) < 8) {
                    $this->flash('error', 'New password must be at least 8 characters.');
                } else {
                    $newHash = password_hash($new, PASSWORD_BCRYPT);
                    $stmt = $db->prepare('UPDATE users SET password = ? WHERE user_id = ?');
                    $stmt->bind_param('si', $newHash, $userId);
                    $stmt->execute();
                    $stmt->close();
                    $this->flash('success', 'Password changed.');
                }
                $this->redirect('/profile');
            }
        }

        $stmt = $db->prepare('SELECT username, email FROM users WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($username, $email);
        $stmt->fetch();
        $stmt->close();

        $this->view('profile/index', compact('username', 'email'), 'main', [
            'page_title' => 'Profile', 'current' => 'profile',
        ]);
    }
}
