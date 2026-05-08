<?php /** @var array $order */ /** @var array $items */ /** @var int $orderId */ /** @var bool $isAdmin */ ?>
<div class="row"><div class="col-md-12">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-4" data-translate="order_details">Order Details #<?= e($order['id']) ?></h1>
        <div class="d-flex">
            <form method="POST" action="<?= url('/order-details?order_id=' . $orderId) ?>" class="me-2">
                <?= csrf_field() ?>
                <button type="submit" name="reorder" class="btn btn-info" data-translate="reorder">Reorder</button>
            </form>
            <form method="POST" action="<?= url('/order-email') ?>" class="me-2">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= e($orderId) ?>">
                <button type="submit" class="btn btn-primary" data-translate="sendOrderEmail">Resend Email</button>
            </form>
            <?php if ($isAdmin): ?>
                <form method="POST" action="<?= url('/order-details?order_id=' . $orderId) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="delete_order" value="<?= e($order['id']) ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete order?')"><i class="fas fa-trash"></i> Delete</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <p><strong data-translate="client_name">Client Name:</strong> <?= e($order['client_name']) ?></p>
    <p><strong data-translate="orderDate">Order Date:</strong> <?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></p>
    <p><strong data-translate="totalValue">Total:</strong> &euro; <?= e(number_format($order['total_amount'], 2, ',', '.')) ?></p>

    <h4 class="mt-4" data-translate="orderItems">Order Items</h4>
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead><tr>
                <th data-translate="product">Product</th>
                <th data-translate="priceunit">Unit Price</th>
                <th data-translate="quantity">Quantity</th>
                <th data-translate="subtotal">Subtotal</th>
            </tr></thead>
            <tbody>
                <?php foreach ($items as $i): $sub = $i['price'] * $i['quantity']; ?>
                <tr>
                    <td><?= e($i['name']) ?></td>
                    <td>&euro; <?= e(number_format($i['price'], 2, ',', '.')) ?></td>
                    <td><?= e($i['quantity']) ?></td>
                    <td>&euro; <?= e(number_format($sub, 2, ',', '.')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div></div>
