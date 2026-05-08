<?php
include 'header.php';
include 'translations.php';
require_once 'helpers.php';
$page_title = 'Users';

// Check if user is logged in and if the user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Location: dashboard.php');
    exit();
}

// $mysqli is already available from dbconnect.php (included via header.php)

// Verify CSRF on all POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
}

// Processa a adição de um novo utilizador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role_id  = intval($_POST['role_id']);

    if (!empty($username) && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 8) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt_insert = $mysqli->prepare('INSERT INTO users (username, email, password, role_id, is_active) VALUES (?, ?, ?, ?, 1)');
        $stmt_insert->bind_param('sssi', $username, $email, $hashed_password, $role_id);
        if ($stmt_insert->execute()) {
            $_SESSION['success_message'] = translate('userAdded', $translations);
        } else {
            $_SESSION['error_message'] = translate('errorUserAdded', $translations);
        }
        $stmt_insert->close();
    } else {
        $_SESSION['error_message'] = translate('validateFields', $translations) . ' (password minimum 8 characters)';
    }
}

// Processa a atualização de utilizadores
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $user_id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role_id = intval($_POST['role_id']);
    $password = trim($_POST['password']);

    if (!empty($password) && strlen($password) < 8) {
        $_SESSION['error_message'] = 'Password must be at least 8 characters.';
        header('Location: users.php');
        exit();
    }

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare('UPDATE users SET username = ?, email = ?, role_id = ?, password = ? WHERE user_id = ?');
        $stmt->bind_param('ssisi', $username, $email, $role_id, $hashed_password, $user_id);
    } else {
        $stmt = $mysqli->prepare('UPDATE users SET username = ?, email = ?, role_id = ? WHERE user_id = ?');
        $stmt->bind_param('ssii', $username, $email, $role_id, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = translate('userUpdated',$translations);
    } else {
        $_SESSION['error_message'] = translate('error',$translations);
    }

    $stmt->close();
    header('Location: users.php');
    exit();
}


// Processa a atualização, inativação ou reativação de utilizadores
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $edit_user_id = intval($_POST['user_id']);

    // Atualiza o papel do utilizador
    if (isset($_POST['role_id'])) {
        $new_role_id = intval($_POST['role_id']);
        if ($edit_user_id != $_SESSION['user_id']) {
            $stmt_update = $mysqli->prepare('UPDATE users SET role_id = ? WHERE user_id = ?');
            $stmt_update->bind_param('ii', $new_role_id, $edit_user_id);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }

    // Inativa o utilizador
    if (isset($_POST['delete_user'])) {
        if ($edit_user_id != $_SESSION['user_id']) {
            $stmt_inactivate = $mysqli->prepare('UPDATE users SET is_active = 0 WHERE user_id = ?');
            $stmt_inactivate->bind_param('i', $edit_user_id);
            $stmt_inactivate->execute();
            $stmt_inactivate->close();
        }
    }

    // Reativa o utilizador
    if (isset($_POST['reactivate_user'])) {
        $stmt_reactivate = $mysqli->prepare('UPDATE users SET is_active = 1 WHERE user_id = ?');
        $stmt_reactivate->bind_param('i', $edit_user_id);
        $stmt_reactivate->execute();
        $stmt_reactivate->close();
    }
}

// Obtém todos os utilizadores, incluindo os desativados
$sql_active = 'SELECT user_id, username, email, role_id, is_active FROM users WHERE is_active = 1';
$result_active = $mysqli->query($sql_active);
$active_users = $result_active->fetch_all(MYSQLI_ASSOC);
$result_active->free();

$sql_inactive = 'SELECT user_id, username, email, role_id, is_active FROM users WHERE is_active = 0';
$result_inactive = $mysqli->query($sql_inactive);
$inactive_users = $result_inactive->fetch_all(MYSQLI_ASSOC);
$result_inactive->free();

$mysqli->close();
include 'template.php';
?>

<div class="row">
    <div class="col-lg-6 col-md-6 mb-4">  
        <h1 data-translate="users">Users</h1>
    </div>         
    <div class="col-lg-6 col-md-6 mb-4 text-right">
        <button class="btn btn-rounded btn-info" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fa-solid fa-plus fa-1x" aria-hidden="true"></i>
        </button>
    </div>         

    <!-- Modal para adicionar utilizador -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel" data-translate="add">Add</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Role:</label>
                            <select id="role_id" name="role_id" class="form-select" required>
                                <option value="1">Admin</option>
                                <option value="2">User</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="close">Close</button>
                        <button type="submit" name="add_user" data-bs-dismiss="modal" class="btn btn-primary" data-translate="add">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabela de utilizadores ativos -->
    <h3 data-translate="activeUsers">Active Users</h3>
    <div class="table-responsive">
        <table id="Data_Table_4" class="table table-striped table-bordered zero-configuration dataTable table-hover">
            <thead>
                <tr>
                    <th data-translate="name">Name</th>
                    <th data-translate="email">Email</th>
                    <th data-translate="role">Role</th>
                    <th data-translate="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($active_users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td data-translate="<?php echo $user['role_id'] == 1 ? 'admin' : 'user'; ?>">
                            <?php echo $user['role_id'] == 1 ? 'Admin' : 'User'; ?>
                        </td>
                        <td class="text-right">
                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                
                                
                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                        onclick='populateEditModalUser(<?php echo json_encode($user); ?>)' data-translate="edit">Edit</button>  
                                

                                
                                <form method="POST" action="" class="d-inline-block">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="role_id" value="<?php echo $user['role_id'] == 1 ? 2 : 1; ?>">
                                    <button type="submit" class="btn btn-warning" data-translate="<?php echo $user['role_id'] == 1 ? 'revokeAdmin' : 'makeAdmin'; ?>">
                                        <?php echo $user['role_id'] == 1 ? 'Revoke Admin' : 'Make Admin'; ?>
                                    </button>
                                </form>
                                <form method="POST" action="" class="d-inline-block ms-2">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="delete_user" value="1">
                                    <button type="submit" class="btn btn-danger" data-translate="deActivate">Deactivate</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted" data-translate="na">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Tabela de utilizadores inativos -->
    <h3 data-translate="inActiveUsers">Inactive Users</h3>
    <div class="table-responsive">
        <table id="Data_Table_5" class="table table-striped table-bordered zero-configuration dataTable table-hover">
            <thead>
                <tr>
                    <th data-translate="name">Name</th>
                    <th data-translate="email">Email</th>
                    <th data-translate="role">Role</th>
                    <th data-translate="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inactive_users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td data-translate="<?php echo $user['role_id'] == 1 ? 'admin' : 'user'; ?>">
                            <?php echo $user['role_id'] == 1 ? 'Admin' : 'User'; ?>
                        </td>
                        <td class="text-right">
                            <form method="POST" action="" class="d-inline-block">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <input type="hidden" name="reactivate_user" value="1">
                                <button type="submit" class="btn btn-success" data-translate="reactivate">Reactivate</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


    <!-- Modal para adicionar/editar utilizador -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel" data-translate="edit">Edit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" id="editUserForm">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username:</label>
                            <input type="text" id="edit_username" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email:</label>
                            <input type="email" id="edit_email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label" data-translate="passBlank">Password (leave blank to keep current):</label>
                            <input type="password" id="edit_password" name="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit_role_id" class="form-label">Role:</label>
                            <select id="edit_role_id" name="role_id" class="form-select" required>
                                <option value="1">Admin</option>
                                <option value="2">User</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="close">Close</button>
                        <button type="submit" name="edit_user" data-bs-dismiss="modal" class="btn btn-primary" data-translate="update">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php include 'footer.php'; ?>