<?php
$current = $current ?? '';
$cartCount = is_array($_SESSION['cart'] ?? null)
    ? array_sum(array_map(fn ($i) => (int) ($i['quantity'] ?? 0), $_SESSION['cart']))
    : 0;
$items = [
    ['key' => 'dashboard',      'href' => '/dashboard',      'icon' => 'fa-house',         'label' => 'Home'],
    ['key' => 'order_products', 'href' => '/order-products', 'icon' => 'fa-shop',          'label' => 'Order'],
    ['key' => 'cart',           'href' => '/cart',           'icon' => 'fa-cart-shopping', 'label' => 'Cart',  'badge' => $cartCount],
    ['key' => 'my_orders',      'href' => '/my-orders',      'icon' => 'fa-list-check',    'label' => 'My orders'],
    ['key' => 'profile',        'href' => '/profile',        'icon' => 'fa-user',          'label' => 'Profile'],
];
?>
<nav class="bottom-nav d-lg-none" aria-label="Primary">
    <ul class="bottom-nav__list">
        <?php foreach ($items as $item): ?>
            <li class="bottom-nav__item">
                <a class="bottom-nav__link <?= $current === $item['key'] ? 'is-active' : '' ?>" href="<?= url($item['href']) ?>">
                    <i class="fa-solid <?= $item['icon'] ?>"></i>
                    <span><?= e($item['label']) ?></span>
                    <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
                        <span class="bottom-nav__badge"><?= e($item['badge']) ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
