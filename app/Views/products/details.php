<?php /** @var array $product */ /** @var array $images */ /** @var array $categories */ ?>
<div class="row">
    <h1 data-translate="productDetails">Product Details</h1>
    <div class="card shadow-sm p-4">
        <form method="POST" action="<?= url('/products/details?product_id=' . $product['id']) ?>" enctype="multipart/form-data" class="row g-4">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= e($product['id']) ?>">

            <div class="col-lg-6"><div class="card p-3 border-0">
                <div class="mb-3"><label class="form-label" data-translate="name">Name</label>
                    <input type="text" class="form-control" name="name" value="<?= e($product['name']) ?>" required></div>
                <div class="mb-3"><label class="form-label" data-translate="reference">Reference</label>
                    <input type="text" class="form-control" name="reference" value="<?= e($product['reference']) ?>" required></div>
                <div class="mb-3"><label class="form-label" data-translate="description">Description</label>
                    <textarea class="form-control" name="description" rows="3" required><?= e($product['description']) ?></textarea></div>
                <div class="mb-3"><label class="form-label" data-translate="category">Category</label>
                    <select class="form-control" name="category_id" required>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= e($c['id']) ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="mb-3"><label class="form-label" data-translate="price">Price</label>
                    <input type="number" class="form-control" name="price" value="<?= e($product['price']) ?>" step="0.01" required></div>
                <div class="mb-3"><label class="form-label" data-translate="priceWvat">Price w/VAT</label>
                    <input type="number" class="form-control" name="pricevat" value="<?= e($product['pricevat']) ?>" step="0.01" required></div>
                <div class="mb-3"><label class="form-label" data-translate="stock">Stock</label>
                    <input type="number" class="form-control" name="stock" value="<?= e($product['stock']) ?>" required></div>
            </div></div>

            <div class="col-lg-6"><div class="card p-3 border-0">
                <h5 class="mb-3" data-translate="uploadImage">Upload Image</h5>
                <div class="mb-3"><input type="file" class="form-control" name="product_image"></div>
                <div class="row">
                    <?php foreach ($images as $img): ?>
                        <div class="col-md-4"><img src="<?= asset($img['image_path']) ?>" class="img-fluid img-thumbnail" alt=""></div>
                    <?php endforeach; ?>
                </div>
            </div></div>

            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary" name="edit_product" data-translate="saveChanges">
                    <i class="fa fa-save me-2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
