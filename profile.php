<?php
include 'header.php';
include 'translations.php';
require_once 'helpers.php';
$page_title = 'Profile';
// $mysqli already available from dbconnect.php via header.php


if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        $stmt = $mysqli->prepare('UPDATE users SET username = ?, email = ? WHERE user_id = ?');
        $stmt->bind_param('ssi', $username, $email, $user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = translate('updatedData',$translations);
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verifica a senha atual
        $stmt = $mysqli->prepare('SELECT password FROM users WHERE user_id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current_password, $hashed_password)) {
            $_SESSION['error_message'] = 'A senha atual está incorreta.';
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['error_message'] = 'A nova senha e a confirmação não coincidem.';
        } elseif (strlen($new_password) < 8) {
            $_SESSION['error_message'] = 'A nova senha deve ter pelo menos 8 caracteres.';
        } else {
            // Atualiza a senha
            $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $mysqli->prepare('UPDATE users SET password = ? WHERE user_id = ?');
            $stmt->bind_param('si', $new_hashed_password, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success_message'] = 'Senha alterada com sucesso!';
        }
    }
}

// Obtém os dados do utilizador
$stmt = $mysqli->prepare('SELECT username, email FROM users WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

$mysqli->close();
include 'template.php';
?>
<h1 data-translate="myProfile">My Profile</h1>
<div class="row">

    <h2 data-translate="updateProfile" mt-4>Update Profile Information</h2>
    <form method="POST" action="" class="mb-4">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <button type="submit" name="update_profile" class="btn btn-primary" data-translate="updateProfile">Update Profile</button>
    </form>

    <h2 class="mt-4" data-translate="changePassword">Change Password</h2>
    <form method="POST" action="">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="current_password" class="form-label" data-translate="currPassword">Current Password:</label>
            <input type="password" id="current_password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label" data-translate="newPassword">New Password:</label>
            <input type="password" id="new_password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label" data-translate="confirmNewPassword">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" name="change_password" class="btn btn-primary" data-translate="changePassword">Change Password</button>
    </form>
</div>
<?php include 'footer.php'; ?>
