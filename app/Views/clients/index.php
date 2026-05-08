<?php /** @var array $clients */ ?>
<div class="row">
    <div class="col-lg-6 col-md-6 mb-4"><h1 data-translate="clients">Clients</h1></div>
    <div class="col-lg-6 col-md-6 mb-4 text-right">
        <button class="btn btn-rounded btn-info" data-bs-toggle="modal" data-bs-target="#addClientModal"><i class="fa-solid fa-plus"></i></button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead><tr>
                <th data-translate="name">Name</th>
                <th data-translate="nif">NIF</th>
                <th data-translate="email">Email</th>
                <th data-translate="phone">Phone</th>
                <th data-translate="city">City</th>
                <th data-translate="state">State</th>
                <th data-translate="actions">Actions</th>
            </tr></thead>
            <tbody>
                <?php foreach ($clients as $c): ?>
                <tr>
                    <td><?= e($c['name']) ?></td>
                    <td><?= e($c['nif']) ?></td>
                    <td><?= e($c['email']) ?></td>
                    <td><?= e($c['phone']) ?></td>
                    <td><?= e($c['city']) ?></td>
                    <td><?= e($c['state']) ?></td>
                    <td class="text-right">
                        <button class="btn btn-warning edit-client-btn" data-bs-toggle="modal" data-bs-target="#editClientModal" data-client='<?= json_encode($c) ?>'>Edit</button>
                        <form method="POST" action="<?= url('/clients') ?>" class="d-inline-block">
                            <?= csrf_field() ?>
                            <input type="hidden" name="delete_client" value="1">
                            <input type="hidden" name="client_id" value="<?= e($c['id']) ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                        <a href="<?= url('/clients/details?client_id=' . $c['id']) ?>" class="btn btn-primary">View Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$fields = ['name'=>'Name','nif'=>'NIF','email'=>'Email','phone'=>'Phone','address'=>'Address','city'=>'City','state'=>'State','zip'=>'ZIP'];
?>

<div id="addClientModal" class="modal fade" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="<?= url('/clients') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="add_client" value="1">
        <div class="modal-header"><h5>Add Client</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <?php foreach ($fields as $k => $label): ?>
                <div class="mb-3"><label class="form-label"><?= e($label) ?></label>
                    <input type="<?= $k === 'email' ? 'email' : 'text' ?>" name="<?= $k ?>" class="form-control" <?= in_array($k,['name','nif']) ? 'required' : '' ?>></div>
            <?php endforeach; ?>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Add</button></div>
    </form>
</div></div></div>

<div id="editClientModal" class="modal fade" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="<?= url('/clients') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="edit_client" value="1">
        <input type="hidden" id="edit_client_id" name="client_id">
        <div class="modal-header"><h5>Edit Client</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <?php foreach ($fields as $k => $label): ?>
                <div class="mb-3"><label class="form-label"><?= e($label) ?></label>
                    <input type="<?= $k === 'email' ? 'email' : 'text' ?>" id="edit_<?= $k ?>" name="<?= $k ?>" class="form-control"></div>
            <?php endforeach; ?>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-client-btn').forEach(b => {
        b.addEventListener('click', function () {
            const c = JSON.parse(this.getAttribute('data-client'));
            for (const k of ['name','nif','email','phone','address','city','state','zip']) {
                const el = document.getElementById('edit_' + k);
                if (el) el.value = c[k] ?? '';
            }
            document.getElementById('edit_client_id').value = c.id;
        });
    });
});
</script>
