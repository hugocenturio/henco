<?php /** @var array $products */ /** @var array $categories */ ?>
<div class="row align-items-center mb-3">
    <div class="col"><h1 class="mb-0" data-translate="orderProducts">Order Products</h1></div>
    <div class="col text-end">
        <a href="<?= url('/cart') ?>" class="btn btn-primary" data-translate="checkout">
            <i class="fas fa-shopping-cart"></i> Checkout
        </a>
    </div>
</div>

<div class="category-chips mb-3" id="categoryChips" role="toolbar" aria-label="Categories">
    <button type="button" class="category-chip is-active" data-category="">All</button>
    <?php foreach ($categories as $c): ?>
        <button type="button" class="category-chip" data-category="<?= e($c['name']) ?>"><?= e($c['name']) ?></button>
    <?php endforeach; ?>
</div>

<input type="search" id="productSearch" class="form-control mb-3" placeholder="Search products…" autocomplete="off">

<!-- Mobile / small viewports -->
<div class="product-cards" id="productCards">
    <?php foreach ($products as $p): ?>
        <article class="product-card js-product"
                 data-name="<?= e(strtolower($p['name'])) ?>"
                 data-reference="<?= e(strtolower($p['reference'])) ?>"
                 data-category="<?= e($p['category_name'] ?? '') ?>">
            <div class="product-card__top">
                <div>
                    <div class="product-card__name"><?= e($p['name']) ?></div>
                    <div class="product-card__meta">
                        <?= e($p['reference']) ?>
                        <?php if (!empty($p['category_name'])): ?> · <?= e($p['category_name']) ?><?php endif; ?>
                        · <span data-translate="stock">Stock</span>: <?= e($p['stock']) ?>
                    </div>
                </div>
                <div class="product-card__price">€ <?= e(number_format($p['pricevat'], 2, ',', '.')) ?></div>
            </div>
            <form method="POST" action="<?= url('/order-products') ?>" class="product-card__row">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
                <div class="qty">
                    <button type="button" class="btn btn-secondary" data-qty="-1" aria-label="Decrease">−</button>
                    <input type="number" class="form-control qty-input" name="quantity" value="1" min="1" max="<?= e($p['stock']) ?>" inputmode="numeric" required>
                    <button type="button" class="btn btn-secondary" data-qty="+1" aria-label="Increase">+</button>
                </div>
                <button type="submit" name="add_to_cart" class="btn btn-primary add" data-translate="add">
                    <i class="fa-solid fa-plus me-1"></i> Add to cart
                </button>
            </form>
        </article>
    <?php endforeach; ?>
    <?php if (empty($products)): ?>
        <div class="text-center text-muted py-5" data-translate="noProducts">No products found.</div>
    <?php endif; ?>
</div>

<!-- Desktop / md+ -->
<div class="product-table">
    <div class="table-responsive-md">
        <table class="table table-striped table-bordered table-hover">
            <thead><tr>
                <th data-translate="category">Category</th>
                <th data-translate="reference">Reference</th>
                <th data-translate="name">Name</th>
                <th data-translate="pricewvat">Price w/VAT</th>
                <th data-translate="stock">Stock</th>
                <th data-translate="quantity">Quantity</th>
            </tr></thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr class="js-product"
                        data-name="<?= e(strtolower($p['name'])) ?>"
                        data-reference="<?= e(strtolower($p['reference'])) ?>"
                        data-category="<?= e($p['category_name'] ?? '') ?>">
                        <td><?= e($p['category_name'] ?? '') ?></td>
                        <td><?= e($p['reference']) ?></td>
                        <td><?= e($p['name']) ?></td>
                        <td>€ <?= e(number_format($p['pricevat'], 2, ',', '.')) ?></td>
                        <td><?= e($p['stock']) ?></td>
                        <td style="text-align:right">
                            <form method="POST" action="<?= url('/order-products') ?>" class="d-inline-block">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="quantity" style="width:70px" value="1" min="1" max="<?= e($p['stock']) ?>" required>
                                    <button type="submit" name="add_to_cart" class="btn btn-primary" data-translate="add">Add</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <tr><td colspan="6" class="text-center">No products found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    // Quantity stepper buttons
    document.querySelectorAll('.product-card .qty .btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.parentElement.querySelector('.qty-input');
            const step = this.dataset.qty === '-1' ? -1 : 1;
            const next = parseInt(input.value || '0', 10) + step;
            const min = parseInt(input.min || '1', 10);
            const max = parseInt(input.max || '999', 10);
            input.value = Math.max(min, Math.min(max, next));
        });
    });

    // Filter (category chips + search)
    const chips = document.querySelectorAll('.category-chip');
    const search = document.getElementById('productSearch');
    let activeCategory = '';

    function applyFilter() {
        const term = (search?.value || '').trim().toLowerCase();
        document.querySelectorAll('.js-product').forEach(el => {
            const matchesCategory = !activeCategory || el.dataset.category === activeCategory;
            const haystack = (el.dataset.name || '') + ' ' + (el.dataset.reference || '');
            const matchesSearch = !term || haystack.includes(term);
            el.style.display = (matchesCategory && matchesSearch) ? '' : 'none';
        });
    }

    chips.forEach(chip => {
        chip.addEventListener('click', function () {
            chips.forEach(c => c.classList.remove('is-active'));
            this.classList.add('is-active');
            activeCategory = this.dataset.category;
            applyFilter();
        });
    });
    if (search) search.addEventListener('input', applyFilter);
})();
</script>
