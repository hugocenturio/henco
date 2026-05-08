<?php /** @var string $message */ ?>
<div class="row">
    <h1 class="mb-4" data-translate="uploadProducts">Import Products</h1>
    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>

    <form action="<?= url('/products/upload') ?>" method="POST" enctype="multipart/form-data" class="mb-4">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="csv_file" class="form-label" data-translate="selectFile">Select CSV File</label>
            <input type="file" id="csv_file" name="csv_file" class="form-control" accept=".csv" required>
        </div>
        <button type="submit" class="btn btn-primary" data-translate="uploadProducts">Import Products</button>
    </form>

    <div class="card"><div class="card-body">
        <h5 class="card-title" data-translate="csvformat">CSV Format</h5>
        <pre class="bg-light p-3">name;reference;description;price;pricevat;stock;category_id
"Product 1";"REF001";"Description 1";100.00;123.00;50;1</pre>
    </div></div>
</div>
