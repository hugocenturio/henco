<?php

namespace App\Controllers;

use App\Core\Controller;

class UserController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $db = $this->db();
        $currentUserId = (int) $_SESSION['user_id'];

        if ($this->request->isPost()) {
            csrf_verify();

            if (isset($_POST['add_user'])) {
                $username = trim($_POST['username'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $roleId   = (int) ($_POST['role_id'] ?? 2);
                if ($username && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 8) {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare('INSERT INTO users (username, email, password, role_id, is_active) VALUES (?, ?, ?, ?, 1)');
                    $stmt->bind_param('sssi', $username, $email, $hash, $roleId);
                    $stmt->execute() ? $this->flash('success', 'User added.') : $this->flash('error', 'Could not add user.');
                    $stmt->close();
                } else {
                    $this->flash('error', 'Invalid input (password must be at least 8 chars).');
                }
            } elseif (isset($_POST['edit_user'])) {
                $id       = (int) ($_POST['user_id'] ?? 0);
                $username = trim($_POST['username'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                $roleId   = (int) ($_POST['role_id'] ?? 2);
                $password = $_POST['password'] ?? '';
                if ($password !== '' && strlen($password) < 8) {
                    $this->flash('error', 'Password must be at least 8 characters.');
                } elseif ($password !== '') {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, role_id = ?, password = ? WHERE user_id = ?');
                    $stmt->bind_param('ssisi', $username, $email, $roleId, $hash, $id);
                    $stmt->execute(); $stmt->close();
                    $this->flash('success', 'User updated.');
                } else {
                    $stmt = $db->prepare('UPDATE users SET username = ?, email = ?, role_id = ? WHERE user_id = ?');
                    $stmt->bind_param('ssii', $username, $email, $roleId, $id);
                    $stmt->execute(); $stmt->close();
                    $this->flash('success', 'User updated.');
                }
            } elseif (isset($_POST['user_id'])) {
                $id = (int) $_POST['user_id'];
                if (isset($_POST['role_id']) && $id !== $currentUserId) {
                    $newRole = (int) $_POST['role_id'];
                    $stmt = $db->prepare('UPDATE users SET role_id = ? WHERE user_id = ?');
                    $stmt->bind_param('ii', $newRole, $id);
                    $stmt->execute(); $stmt->close();
                }
                if (isset($_POST['delete_user']) && $id !== $currentUserId) {
                    $stmt = $db->prepare('UPDATE users SET is_active = 0 WHERE user_id = ?');
                    $stmt->bind_param('i', $id);
                    $stmt->execute(); $stmt->close();
                    $this->flash('success', 'User deactivated.');
                }
                if (isset($_POST['reactivate_user'])) {
                    $stmt = $db->prepare('UPDATE users SET is_active = 1 WHERE user_id = ?');
                    $stmt->bind_param('i', $id);
                    $stmt->execute(); $stmt->close();
                    $this->flash('success', 'User reactivated.');
                }
            }
            $this->redirect('/users');
        }

        $active   = $db->query('SELECT user_id, username, email, role_id, is_active FROM users WHERE is_active = 1')->fetch_all(MYSQLI_ASSOC);
        $inactive = $db->query('SELECT user_id, username, email, role_id, is_active FROM users WHERE is_active = 0')->fetch_all(MYSQLI_ASSOC);

        $this->view('users/index', [
            'activeUsers'   => $active,
            'inactiveUsers' => $inactive,
            'currentUserId' => $currentUserId,
        ], 'main', ['page_title' => 'Users', 'current' => 'users']);
    }
}
