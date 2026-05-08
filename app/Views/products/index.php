<?php /** @var array $products */ /** @var array $categories */ ?>
<div class="row">
    <div class="col-lg-6 col-md-6 mb-4"><h1 data-translate="products">Products</h1></div>
    <div class="col-lg-6 col-md-6 mb-4 text-right">
        <button class="btn btn-rounded btn-info" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="fa-solid fa-plus"></i></button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead><tr>
                <th data-translate="name">Name</th>
                <th data-translate="reference">Reference</th>
                <th data-translate="description">Description</th>
                <th data-translate="price">Price</th>
                <th data-translate="priceWvat">Price w/VAT</th>
                <th data-translate="stock">Stock</th>
                <th data-translate="category">Category</th>
                <th data-translate="actions">Actions</th>
            </tr></thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= e($p['name']) ?></td>
                    <td><?= e($p['reference']) ?></td>
                    <td><?= e($p['description']) ?></td>
                    <td>€ <?= e(number_format($p['price'], 2, ',', '.')) ?></td>
                    <td>€ <?= e(number_format($p['pricevat'], 2, ',', '.')) ?></td>
                    <td><?= e($p['stock']) ?></td>
                    <td><?= e($p['category_name'] ?? '') ?></td>
                    <td class="text-right">
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal" onclick='populateEditModalProduct(<?= json_encode($p) ?>)' data-translate="edit">Edit</button>
                        <form method="POST" action="<?= url('/products') ?>" class="d-inline-block">
                            <?= csrf_field() ?>
                            <input type="hidden" name="delete_product" value="1">
                            <input type="hidden" name="product_id" value="<?= e($p['id']) ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Delete?')" data-translate="delete">Delete</button>
                        </form>
                        <a href="<?= url('/products/details?product_id=' . $p['id']) ?>" class="btn btn-primary" data-translate="viewDetails">View Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addProductModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?= url('/products') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="add_product" value="1">
            <div class="modal-header"><h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <?php foreach (['name','reference','description'] as $f): ?>
                    <div class="mb-3"><label class="form-label" data-translate="<?= $f ?>"><?= ucfirst($f) ?></label>
                        <input type="text" name="<?= $f ?>" class="form-control" required></div>
                <?php endforeach; ?>
                <?php foreach (['price','pricevat'] as $f): ?>
                    <div class="mb-3"><label class="form-label" data-translate="<?= $f ?>"><?= $f ?></label>
                        <input type="number" step="0.01" name="<?= $f ?>" class="form-control" required></div>
                <?php endforeach; ?>
                <div class="mb-3"><label class="form-label" data-translate="stock">Stock</label>
                    <input type="number" name="stock" class="form-control" required></div>
                <div class="mb-3"><label class="form-label" data-translate="category">Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="" disabled selected>-- Select --</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Add</button></div>
        </form>
    </div></div>
</div>

<div id="editProductModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <form id="updateProductForm" method="POST" action="<?= url('/products') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="edit_product" value="1">
            <input type="hidden" id="edit_product_id" name="product_id">
            <div class="modal-header"><h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <?php foreach (['name','reference','description'] as $f): ?>
                    <div class="mb-3"><label class="form-label"><?= ucfirst($f) ?></label>
                        <input type="text" id="edit_<?= $f ?>" name="<?= $f ?>" class="form-control" required></div>
                <?php endforeach; ?>
                <?php foreach (['price','pricevat'] as $f): ?>
                    <div class="mb-3"><label class="form-label"><?= $f ?></label>
                        <input type="number" step="0.01" id="edit_<?= $f ?>" name="<?= $f ?>" class="form-control" required></div>
                <?php endforeach; ?>
                <div class="mb-3"><label class="form-label">Stock</label>
                    <input type="number" id="edit_stock" name="stock" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Category</label>
                    <select id="edit_category_id" name="category_id" class="form-select" required>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
        </form>
    </div></div>
</div>

<script>
function populateEditModalProduct(p) {
    document.getElementById('edit_product_id').value = p.id;
    document.getElementById('edit_name').value = p.name;
    document.getElementById('edit_reference').value = p.reference;
    document.getElementById('edit_description').value = p.description;
    document.getElementById('edit_price').value = p.price;
    document.getElementById('edit_pricevat').value = p.pricevat;
    document.getElementById('edit_stock').value = p.stock;
    document.getElementById('edit_category_id').value = p.category_id;
}
</script>
