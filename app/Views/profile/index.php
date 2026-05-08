<?php /** @var string $username */ /** @var string $email */ ?>
<h1 data-translate="myProfile">My Profile</h1>
<div class="row">
    <h2 data-translate="updateProfile">Update Profile Information</h2>
    <form method="POST" action="<?= url('/profile') ?>" class="mb-4">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" id="username" name="username" class="form-control" value="<?= e($username) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= e($email) ?>" required>
        </div>
        <button type="submit" name="update_profile" class="btn btn-primary" data-translate="updateProfile">Update Profile</button>
    </form>

    <h2 class="mt-4" data-translate="changePassword">Change Password</h2>
    <form method="POST" action="<?= url('/profile') ?>">
        <?= csrf_field() ?>
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
