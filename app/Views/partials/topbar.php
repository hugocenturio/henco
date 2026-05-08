<?php
$isAdmin       = !empty($_SESSION['is_admin']);
$reorderId     = $_SESSION['reorder_order_id'] ?? null;
$username      = $_SESSION['username'] ?? 'Guest';
$notifications = $isAdmin ? \App\Controllers\NotificationController::fetchUnread() : [];
$totalUnread   = count($notifications);
?>
<div class="header">
    <div class="header-content clearfix">
        <div class="nav-control">
            <div class="hamburger is-active">
                <span class="toggle-icon"><i class="icon-menu"></i></span>
            </div>
        </div>
        <div class="header-left">
            <div class="input-group icons">
                <div class="input-group-prepend">
                    <?php if ($reorderId): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <span data-translate="orderInContext">You are reordering from Order</span> #<?= e($reorderId) ?>
                            <form method="POST" action="<?= url('/dashboard') ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" name="clear_reorder" class="close" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="header-right">
            <ul class="clearfix">
                <?php if ($isAdmin): ?>
                    <li class="icons dropdown">
                        <a href="javascript:void(0)" id="notificationIcon" data-toggle="dropdown">
                            <i class="mdi mdi-email-outline"></i>
                            <?php if ($totalUnread > 0): ?>
                                <span class="badge gradient-1 badge-pill badge-primary"><?= e($totalUnread) ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="drop-down animated fadeIn dropdown-menu" id="notificationDropdown">
                            <div class="dropdown-content-heading d-flex justify-content-between">
                                <span>
                                    <?= e($totalUnread) ?>
                                    <?= $totalUnread === 1 ? 'New Notification' : 'New Notifications' ?>
                                </span>
                            </div>
                            <div class="dropdown-content-body">
                                <ul>
                                    <?php if ($notifications): foreach ($notifications as $n): ?>
                                        <li class="notification-unread">
                                            <a href="javascript:void(0)">
                                                <div class="notification-content">
                                                    <div class="notification-heading">Notification</div>
                                                    <div class="notification-timestamp">
                                                        <?= e(date('d/m/Y H:i', strtotime($n['created_at']))) ?>
                                                    </div>
                                                    <div class="notification-text"><?= e($n['message']) ?></div>
                                                </div>
                                            </a>
                                        </li>
                                    <?php endforeach; else: ?>
                                        <li>
                                            <div class="notification-content text-muted text-center">No new notifications</div>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <script src="<?= asset('js/notifications.js') ?>"></script>
                <?php endif; ?>

                <li class="icons">
                    <a class="nav-link" href="<?= url('/cart') ?>">
                        <i id="cart" class="ti-shopping-cart"></i><span data-translate="cart"> Cart</span>
                    </a>
                </li>
                <li class="icons">
                    <a href="<?= url('/profile') ?>" data-translate="guest"><?= e($username) ?></a>
                </li>
                <li class="icons">
                    <a href="<?= url('/logout') ?>" data-translate="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</div>
