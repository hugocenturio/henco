<?php /** @var array $orders */ ?>
<div class="row">
    <h1 data-translate="orderHistory">Order History</h1>
    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead><tr>
                    <th data-translate="orderNumber">Order Number</th>
                    <th data-translate="user">User</th>
                    <th data-translate="date">Date</th>
                    <th data-translate="totalValue">Total</th>
                    <th data-translate="shipped">Shipped</th>
                    <th data-translate="actions">Actions</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><?= e($o['id']) ?></td>
                            <td><?= e($o['username']) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></td>
                            <td>€ <?= e(number_format($o['total_amount'], 2, ',', '.')) ?></td>
                            <td>
                                <?php if ($o['shipped'] == 1): ?>
                                    <span class="badge bg-success">Shipped <?= e(date('d/m/Y H:i', strtotime($o['shipped_at']))) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <a href="<?= url('/order-details?order_id=' . $o['id']) ?>" class="btn btn-primary" data-translate="viewDetails">View</a>
                                <form method="POST" action="<?= url('/order-history') ?>" class="d-inline-block">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="order_id" value="<?= e($o['id']) ?>">
                                    <?php if ($o['shipped'] == 1): ?>
                                        <button type="submit" name="mark_shipped" value="0" class="btn btn-danger" onclick="return confirm('Revert?')">Revert</button>
                                    <?php else: ?>
                                        <button type="submit" name="mark_shipped" value="1" class="btn btn-success" onclick="return confirm('Mark as shipped?')">Ship</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center" data-translate="noOrdersFound">No orders found.</p>
    <?php endif; ?>
</div>
