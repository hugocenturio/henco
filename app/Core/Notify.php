<?php

namespace App\Core;

class Notify
{
    /** Insert a single notification for one user. */
    public static function user(int $userId, string $message): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO notifications (user_id, message) VALUES (?, ?)'
        );
        $stmt->bind_param('is', $userId, $message);
        $stmt->execute();
        $stmt->close();
    }

    /** Insert one notification per active admin (role_id = 1). */
    public static function admins(string $message): void
    {
        $db = Database::connection();
        $rs = $db->query('SELECT user_id FROM users WHERE role_id = 1 AND is_active = 1');
        if (!$rs) return;

        $stmt = $db->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
        while ($row = $rs->fetch_assoc()) {
            $uid = (int) $row['user_id'];
            $stmt->bind_param('is', $uid, $message);
            $stmt->execute();
        }
        $stmt->close();
    }

    /** Count unread notifications for the current user (admins see all). */
    public static function unreadCount(int $userId, bool $isAdmin): int
    {
        $db = Database::connection();
        if ($isAdmin) {
            $row = $db->query('SELECT COUNT(*) AS c FROM notifications WHERE is_read = 0')->fetch_assoc();
        } else {
            $stmt = $db->prepare('SELECT COUNT(*) AS c FROM notifications WHERE is_read = 0 AND user_id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
        return (int) ($row['c'] ?? 0);
    }
}
