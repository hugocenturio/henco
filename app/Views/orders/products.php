<?php /** @var array $products */ /** @var array $categories */ ?>
<div class="row">
    <div class="col-lg-6 col-md-6 mb-4"><h1 data-translate="orderProducts">Order Products</h1></div>
    <div class="col-lg-6 col-md-6 mb-4 text-right">
        <a href="<?= url('/cart') ?>" class="btn btn-primary" data-translate="checkout">
            <i class="fas fa-shopping-cart"></i> Checkout
        </a>
    </div>

    <div class="mb-4">
        <div id="categoryTags" class="d-flex flex-wrap gap-2">
            <i class="fa fa-filter fa-1x"></i>
            <?php foreach ($categories as $c): ?>
                <h4><span class="p-3 badge badge-pill bg-primary text-white category-tag btn-sm" data-category="<?= e($c['name']) ?>"><?= e($c['name']) ?></span></h4>
            <?php endforeach; ?>
        </div>
    </div>

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
                    <tr>
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
                                    <input type="number" class="form-control" name="quantity" style="width:50px" value="1" min="1" max="<?= e($p['stock']) ?>" required>
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
