<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Notify;

class NotificationController extends Controller
{
    /** Full notification list (read + unread). */
    public function index(): void
    {
        $this->requireAuth();
        $db      = $this->db();
        $userId  = (int) $_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);

        if ($this->request->isPost()) {
            csrf_verify();
            if (isset($_POST['mark_all_read'])) {
                if ($isAdmin) {
                    $db->query('UPDATE notifications SET is_read = 1 WHERE is_read = 0');
                } else {
                    $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE is_read = 0 AND user_id = ?');
                    $stmt->bind_param('i', $userId);
                    $stmt->execute();
                    $stmt->close();
                }
            } elseif (isset($_POST['mark_one'])) {
                $id = (int) $_POST['mark_one'];
                if ($isAdmin) {
                    $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ?');
                    $stmt->bind_param('i', $id);
                } else {
                    $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
                    $stmt->bind_param('ii', $id, $userId);
                }
                $stmt->execute();
                $stmt->close();
            } elseif (isset($_POST['delete'])) {
                $id = (int) $_POST['delete'];
                if ($isAdmin) {
                    $stmt = $db->prepare('DELETE FROM notifications WHERE id = ?');
                    $stmt->bind_param('i', $id);
                } else {
                    $stmt = $db->prepare('DELETE FROM notifications WHERE id = ? AND user_id = ?');
                    $stmt->bind_param('ii', $id, $userId);
                }
                $stmt->execute();
                $stmt->close();
            }
            $this->redirect('/notifications');
        }

        if ($isAdmin) {
            $rs = $db->query('SELECT n.*, u.username FROM notifications n LEFT JOIN users u ON u.user_id = n.user_id ORDER BY n.created_at DESC LIMIT 200');
            $rows = $rs ? $rs->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            $stmt = $db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 200');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        $this->view('notifications/index', ['notifications' => $rows], 'main', [
            'page_title' => 'Notifications', 'current' => 'notifications',
        ]);
    }

    /** Mark all visible notifications as read (AJAX). */
    public function markRead(): void
    {
        $this->requireAuth();
        csrf_verify();

        $db      = $this->db();
        $userId  = (int) $_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);

        if ($isAdmin) {
            $db->query('UPDATE notifications SET is_read = 1 WHERE is_read = 0');
        } else {
            $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE is_read = 0 AND user_id = ?');
            $stmt->bind_param('i', $userId); $stmt->execute(); $stmt->close();
        }
        $this->json(['success' => true]);
    }

    /** Lightweight unread-count endpoint for live badge polling. */
    public function unreadCount(): void
    {
        $this->requireAuth();
        $count = Notify::unreadCount(
            (int) $_SESSION['user_id'],
            !empty($_SESSION['is_admin'])
        );
        $this->json(['count' => $count]);
    }

    /**
     * Returns the unread list for the topbar dropdown. Called from the
     * sidebar/topbar partials so the markup keeps working outside of a
     * controller-driven request.
     */
    public static function fetchUnread(): array
    {
        if (empty($_SESSION['user_id'])) {
            return [];
        }
        $db = Database::connection();
        $db->query('DELETE FROM notifications WHERE created_at < NOW() - INTERVAL 30 DAY');

        $userId  = (int) $_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);

        if ($isAdmin) {
            $stmt = $db->prepare('SELECT id, message, created_at FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 20');
        } else {
            $stmt = $db->prepare('SELECT id, message, created_at FROM notifications WHERE is_read = 0 AND user_id = ? ORDER BY created_at DESC LIMIT 20');
            $stmt->bind_param('i', $userId);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}
