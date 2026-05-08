<?php /** @var array $notifications */ ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Notifications</h1>
    <?php if (!empty($notifications)): ?>
        <form method="POST" action="<?= url('/notifications') ?>" class="d-inline-block">
            <?= csrf_field() ?>
            <button type="submit" name="mark_all_read" class="btn btn-outline-primary">
                <i class="fa-solid fa-check-double me-1"></i> Mark all as read
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
    <div class="text-center text-muted py-5">
        <i class="fa-regular fa-bell-slash fa-2x mb-3"></i>
        <p class="mb-0">You're all caught up.</p>
    </div>
<?php else: ?>
    <ul class="list-group list-group-flush">
        <?php foreach ($notifications as $n): ?>
            <li class="list-group-item d-flex flex-wrap align-items-start gap-2 <?= $n['is_read'] ? '' : 'fw-semibold' ?>">
                <div class="flex-grow-1">
                    <div><?= e($n['message']) ?></div>
                    <small class="text-muted">
                        <?= e(date('d/m/Y H:i', strtotime($n['created_at']))) ?>
                        <?php if (!empty($n['username'])): ?>
                            · <?= e($n['username']) ?>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="d-flex gap-1">
                    <?php if (!$n['is_read']): ?>
                        <form method="POST" action="<?= url('/notifications') ?>" class="d-inline-block">
                            <?= csrf_field() ?>
                            <button type="submit" name="mark_one" value="<?= e($n['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Mark as read">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="<?= url('/notifications') ?>" class="d-inline-block">
                        <?= csrf_field() ?>
                        <button type="submit" name="delete" value="<?= e($n['id']) ?>" class="btn btn-sm btn-outline-danger" title="Delete"
                                onclick="return confirm('Delete this notification?');">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
