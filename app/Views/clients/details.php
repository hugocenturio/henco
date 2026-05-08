<?php /** @var array|null $client */ /** @var array $orders */ ?>
<?php if (!$client): ?>
    <div class="alert alert-danger">Client not found.</div>
<?php else: ?>
<div class="row">
    <h1><?= e($client['name']) ?></h1>
    <div class="card mb-4"><div class="card-body">
        <p><strong>NIF:</strong> <?= e($client['nif']) ?></p>
        <p><strong>Email:</strong> <?= e($client['email']) ?></p>
        <p><strong>Phone:</strong> <?= e($client['phone']) ?></p>
        <p><strong>Address:</strong> <?= e($client['address']) ?>, <?= e($client['city']) ?>, <?= e($client['state']) ?> <?= e($client['zip']) ?></p>
    </div></div>

    <h2>Orders</h2>
    <?php if (!empty($orders)): ?>
        <table class="table table-striped">
            <thead><tr><th>#</th><th>Date</th><th>Total</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= e($o['id']) ?></td>
                    <td><?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></td>
                    <td>€ <?= e(number_format($o['total_amount'], 2, ',', '.')) ?></td>
                    <td><a href="<?= url('/order-details?order_id=' . $o['id']) ?>" class="btn btn-primary btn-sm">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">No orders for this client.</p>
    <?php endif; ?>
    <a href="<?= url('/clients') ?>" class="btn btn-secondary">&larr; Back</a>
</div>
<?php endif; ?>
