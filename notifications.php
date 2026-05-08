<?php
session_start();
include 'header.php';

// Limpar notificações com mais de 1 semana
$mysqli->query("DELETE FROM notifications WHERE created_at < NOW() - INTERVAL 7 DAY");

// Admins see all notifications; regular users see only their own
$current_user_id = (int)$_SESSION['user_id'];
$is_admin = isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;

if ($is_admin) {
    $stmt = $mysqli->prepare("SELECT id, message, created_at FROM notifications WHERE is_read = 0 ORDER BY created_at DESC");
    $stmt->execute();
} else {
    $stmt = $mysqli->prepare("SELECT id, message, created_at FROM notifications WHERE is_read = 0 AND user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param('i', $current_user_id);
    $stmt->execute();
}
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$total_unread = count($notifications);
$stmt->close();

// Marcar notificações como lidas se houver solicitação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    if ($is_admin) {
        $mysqli->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
    } else {
        $stmt_mark = $mysqli->prepare("UPDATE notifications SET is_read = 1 WHERE is_read = 0 AND user_id = ?");
        $stmt_mark->bind_param('i', $current_user_id);
        $stmt_mark->execute();
        $stmt_mark->close();
    }
    echo json_encode(['success' => true]);
    exit();
}
?>

<!-- Ícone de Notificações -->
<li class="icons dropdown">
    <a href="javascript:void(0)" id="notificationIcon" data-toggle="dropdown">
        <i class="mdi mdi-email-outline"></i>
        <?php if ($total_unread > 0): ?>
            <span class="badge gradient-1 badge-pill badge-primary">
                <?php echo $total_unread; ?>
            </span>
        <?php endif; ?>
    </a>
    <div class="drop-down animated fadeIn dropdown-menu" id="notificationDropdown">
        <div class="dropdown-content-heading d-flex justify-content-between">
            <span class="">
                <?php echo $total_unread; ?> 
                <?php echo $total_unread == 1 ? 'Nova Notificação' : 'Novas Notificações'; ?>
            </span>
        </div>
        <div class="dropdown-content-body">
            <ul>
                <?php if ($notifications): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <li class="notification-unread">
                            <a href="javascript:void(0)">
                                
                                <div class="notification-content">
                                    <div class="notification-heading">Notificação</div>
                                    <div class="notification-timestamp">
                                        <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                                    </div>
                                    <div class="notification-text">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>
                        <div class="notification-content text-muted text-center">Sem novas notificações</div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</li>
