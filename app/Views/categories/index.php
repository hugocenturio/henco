<?php /** @var array $categories */ /** @var ?string $errorMessage */ ?>
<?php if ($errorMessage): ?>
    <div class="alert alert-danger"><?= e($errorMessage) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-6 col-md-6 mb-4"><h1 data-translate="categories">Categories</h1></div>
    <div class="col-lg-6 col-md-6 mb-4 text-right">
        <button class="btn btn-rounded btn-info" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="fa-solid fa-plus"></i></button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead><tr><th>Id</th><th data-translate="name">Name</th><th data-translate="actions">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= e($c['id']) ?></td>
                    <td><?= e($c['name']) ?></td>
                    <td class="text-right">
                        <button class="btn btn-warning edit-category-btn" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-category='<?= json_encode($c) ?>'>Edit</button>
                        <form method="POST" action="<?= url('/categories') ?>" class="d-inline-block">
                            <?= csrf_field() ?>
                            <input type="hidden" name="delete_category" value="1">
                            <input type="hidden" name="category_id" value="<?= e($c['id']) ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addCategoryModal" class="modal fade" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="<?= url('/categories') ?>">
        <?= csrf_field() ?>
        <div class="modal-header"><h5>Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="add_category" value="1">
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Add</button></div>
    </form>
</div></div></div>

<div id="editCategoryModal" class="modal fade" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="<?= url('/categories') ?>">
        <?= csrf_field() ?>
        <div class="modal-header"><h5>Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="edit_category" value="1">
            <input type="hidden" id="edit_category_id" name="category_id">
            <div class="mb-3"><label class="form-label">Name</label><input type="text" id="edit_name" name="name" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-category-btn').forEach(b => {
        b.addEventListener('click', function () {
            const c = JSON.parse(this.getAttribute('data-category'));
            document.getElementById('edit_category_id').value = c.id;
            document.getElementById('edit_name').value = c.name;
        });
    });
});
</script>
