<?php
$current  = $current ?? '';
$isAdmin  = !empty($_SESSION['is_admin']);
$company  = $_SESSION['company_name'] ?? 'Your Company';
?>
<div class="nav-header">
    <div class="brand-logo">
        <a href="<?= url('/dashboard') ?>">
            <b class="logo-abbr company-name"><?= e(mb_substr($company, 0, 1)) ?></b>
            <span class="logo-compact company-name"><?= e(mb_substr($company, 0, 1)) ?></span>
            <span class="brand-title company-name"><?= e($company) ?></span>
        </a>
    </div>
</div>

<div class="nk-sidebar">
    <div class="slimScrollDiv" style="position:relative;overflow:hidden;width:auto;height:100%;">
        <div class="nk-nav-scroll" style="overflow:hidden;width:auto;height:100%;">
            <ul class="metismenu" id="menu">
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'dashboard' ? 'active' : '' ?>" href="<?= url('/dashboard') ?>">
                        <i class="icon-speedometer menu-icon"></i><span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'order_products' ? 'active' : '' ?>" href="<?= url('/order-products') ?>">
                        <i class="fa-solid fa-shop"></i><span class="nav-text" data-translate="orderProducts"> Order Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'my_orders' ? 'active' : '' ?>" href="<?= url('/my-orders') ?>">
                        <i class="ti-email"></i><span class="nav-text" data-translate="myOrders"> My Orders</span>
                    </a>
                </li>
                <?php if ($isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'products' ? 'active' : '' ?>" href="<?= url('/products') ?>">
                            <i class="ti-dropbox-alt"></i><span id="products" class="nav-text" data-translate="products"> Products</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'categories' ? 'active' : '' ?>" href="<?= url('/categories') ?>">
                            <i class="fa fa-list"></i><span class="nav-text" data-translate="categories"> Categories</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'clients' ? 'active' : '' ?>" href="<?= url('/clients') ?>">
                            <i class="fa-solid fa-user-tie"></i><span class="nav-text" data-translate="clients"> Clients</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'order_history' ? 'active' : '' ?>" href="<?= url('/order-history') ?>">
                            <i class="fas fa-history"></i><span class="nav-text" data-translate="orderHistory"> Order History</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'users' ? 'active' : '' ?>" href="<?= url('/users') ?>">
                            <i class="fas fa-users-cog"></i><span class="nav-text" data-translate="users"> Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'settings' ? 'active' : '' ?>" href="<?= url('/settings') ?>">
                            <i class="fas fa-cog"></i><span class="nav-text" data-translate="settings"> Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'upload' ? 'active' : '' ?>" href="<?= url('/products/upload') ?>">
                            <i class="fas fa-upload"></i><span class="nav-text" data-translate="importProducts"> Import Products</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
