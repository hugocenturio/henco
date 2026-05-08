<?php /** @var array $values */ ?>
<div class="row">
    <h1 data-translate="settings">Settings</h1>
    <form method="POST" action="<?= url('/settings') ?>">
        <?= csrf_field() ?>
        <div class="mb-4"><label class="form-label" data-translate="managerEmail">Manager Email:</label>
            <input type="email" name="manager_email" class="form-control" value="<?= e($values['manager_email']) ?>" required></div>
        <div class="mb-4"><label class="form-label" data-translate="sendEmail">Sender Email:</label>
            <input type="email" name="send_email" class="form-control" value="<?= e($values['send_email']) ?>" required></div>
        <div class="mb-4"><label class="form-label" data-translate="currency">Currency:</label>
            <input type="text" name="currency" class="form-control" value="<?= e($values['currency']) ?>" required></div>
        <div class="mb-4"><label class="form-label" data-translate="locale">Locale:</label>
            <input type="text" name="locale" class="form-control" value="<?= e($values['locale']) ?>" required></div>
        <div class="mb-4"><label class="form-label" data-translate="companyName">Company Name:</label>
            <input type="text" name="company_name" class="form-control" value="<?= e($values['company_name']) ?>" required></div>
        <button class="btn btn-primary" data-translate="update">Update</button>
    </form>
</div>
