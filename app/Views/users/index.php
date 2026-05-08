<?php /** @var array $activeUsers */ /** @var array $inactiveUsers */ /** @var int $currentUserId */ ?>
<div class="row">
    <div class="col-lg-6 col-md-6 mb-4"><h1 data-translate="users">Users</h1></div>
    <div class="col-lg-6 col-md-6 mb-4 text-right">
        <button class="btn btn-rounded btn-info" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fa-solid fa-plus"></i></button>
    </div>

    <h3 data-translate="activeUsers">Active Users</h3>
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($activeUsers as $u): ?>
                <tr>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= $u['role_id'] == 1 ? 'Admin' : 'User' ?></td>
                    <td class="text-right">
                        <?php if ($u['user_id'] != $currentUserId): ?>
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal" onclick='populateEditUser(<?= json_encode($u) ?>)'>Edit</button>
                            <form method="POST" action="<?= url('/users') ?>" class="d-inline-block">
                                <?= csrf_field() ?>
                                <input type="hidden" name="user_id" value="<?= e($u['user_id']) ?>">
                                <input type="hidden" name="role_id" value="<?= $u['role_id'] == 1 ? 2 : 1 ?>">
                                <button class="btn btn-warning"><?= $u['role_id'] == 1 ? 'Revoke Admin' : 'Make Admin' ?></button>
                            </form>
                            <form method="POST" action="<?= url('/users') ?>" class="d-inline-block ms-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="user_id" value="<?= e($u['user_id']) ?>">
                                <input type="hidden" name="delete_user" value="1">
                                <button class="btn btn-danger">Deactivate</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h3 data-translate="inActiveUsers">Inactive Users</h3>
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($inactiveUsers as $u): ?>
                <tr>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= $u['role_id'] == 1 ? 'Admin' : 'User' ?></td>
                    <td class="text-right">
                        <form method="POST" action="<?= url('/users') ?>" class="d-inline-block">
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= e($u['user_id']) ?>">
                            <input type="hidden" name="reactivate_user" value="1">
                            <button class="btn btn-success">Reactivate</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addUserModal" class="modal fade" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="<?= url('/users') ?>">
        <?= csrf_field() ?>
        <div class="modal-header"><h5>Add User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Role</label>
                <select name="role_id" class="form-select" required>
                    <option value="1">Admin</option><option value="2" selected>User</option>
                </select></div>
        </div>
        <div class="modal-footer"><button type="submit" name="add_user" class="btn btn-primary">Add</button></div>
    </form>
</div></div></div>

<div id="editUserModal" class="modal fade" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="<?= url('/users') ?>">
        <?= csrf_field() ?>
        <input type="hidden" id="edit_user_id" name="user_id">
        <div class="modal-header"><h5>Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Username</label><input type="text" id="edit_username" name="username" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" id="edit_email" name="email" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Password (blank to keep)</label><input type="password" id="edit_password" name="password" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Role</label>
                <select id="edit_role_id" name="role_id" class="form-select" required>
                    <option value="1">Admin</option><option value="2">User</option>
                </select></div>
        </div>
        <div class="modal-footer"><button type="submit" name="edit_user" class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

<script>
function populateEditUser(u) {
    document.getElementById('edit_user_id').value = u.user_id;
    document.getElementById('edit_username').value = u.username;
    document.getElementById('edit_email').value = u.email;
    document.getElementById('edit_role_id').value = u.role_id;
    document.getElementById('edit_password').value = '';
}
</script>
