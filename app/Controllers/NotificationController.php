<?php

namespace App\Controllers;

use App\Core\Controller;

class NotificationController extends Controller
{
    /** Mark all notifications visible to the current user as read (AJAX). */
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
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->close();
        }
        $this->json(['success' => true]);
    }

    /** Returns the unread list for rendering inside the topbar partial. */
    public static function fetchUnread(): array
    {
        if (empty($_SESSION['user_id'])) {
            return [];
        }
        $db = \App\Core\Database::connection();
        $db->query('DELETE FROM notifications WHERE created_at < NOW() - INTERVAL 7 DAY');

        $userId  = (int) $_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);

        if ($isAdmin) {
            $stmt = $db->prepare('SELECT id, message, created_at FROM notifications WHERE is_read = 0 ORDER BY created_at DESC');
        } else {
            $stmt = $db->prepare('SELECT id, message, created_at FROM notifications WHERE is_read = 0 AND user_id = ? ORDER BY created_at DESC');
            $stmt->bind_param('i', $userId);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}
