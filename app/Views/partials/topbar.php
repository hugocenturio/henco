<?php
$isAdmin       = !empty($_SESSION['is_admin']);
$reorderId     = $_SESSION['reorder_order_id'] ?? null;
$username      = $_SESSION['username'] ?? 'Guest';
$notifications = !empty($_SESSION['user_id']) ? \App\Controllers\NotificationController::fetchUnread() : [];
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
                <li class="icons">
                    <button type="button" class="theme-toggle" data-theme-toggle aria-label="Toggle theme">
                        <i class="fa-solid fa-moon icon-moon"></i>
                        <i class="fa-solid fa-sun icon-sun"></i>
                    </button>
                </li>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <li class="icons dropdown notifications-dropdown">
                        <a href="javascript:void(0)" id="notificationIcon" data-toggle="dropdown" aria-label="Notifications">
                            <i class="fa-solid fa-bell"></i>
                            <span id="notificationBadge" class="badge gradient-1 badge-pill badge-primary <?= $totalUnread > 0 ? '' : 'd-none' ?>">
                                <?= e($totalUnread) ?>
                            </span>
                        </a>
                        <div class="drop-down animated fadeIn dropdown-menu" id="notificationDropdown">
                            <div class="dropdown-content-heading d-flex justify-content-between align-items-center">
                                <span id="notificationDropdownHeading">
                                    <?= $totalUnread === 1 ? '1 new notification' : e($totalUnread) . ' new notifications' ?>
                                </span>
                                <a href="<?= url('/notifications') ?>" class="small">View all</a>
                            </div>
                            <div class="dropdown-content-body">
                                <ul>
                                    <?php if ($notifications): foreach ($notifications as $n): ?>
                                        <li class="notification-unread">
                                            <a href="<?= url('/notifications') ?>">
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
                                            <div class="notification-content text-muted text-center py-3">No new notifications</div>
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
