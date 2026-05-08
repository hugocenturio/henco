<?php /** @var array $orders */ ?>
<div class="row">
    <h1 data-translate="myOrders">My Orders</h1>
    <?php if (!empty($orders)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead><tr>
                    <th data-translate="orderNumber">Order Number</th>
                    <th data-translate="date">Date</th>
                    <th data-translate="totalAmount">Total Amount</th>
                    <th data-translate="actions">Actions</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?= e($o['id']) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></td>
                            <td>€ <?= e(number_format($o['total_amount'], 2, ',', '.')) ?></td>
                            <td class="text-right">
                                <a href="<?= url('/order-details?order_id=' . $o['id']) ?>" class="btn btn-primary" data-translate="viewDetails">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info" data-translate="noOrders">You have not placed any orders yet.</div>
    <?php endif; ?>
</div>
