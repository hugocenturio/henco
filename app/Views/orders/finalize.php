<?php /** @var array $clients */ /** @var array $cartItems */ /** @var float $total */ ?>
<div class="row">
    <h1 data-translate="finalizeOrder">Finalize Order</h1>

    <form method="POST" action="<?= url('/finalize-order') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label" data-translate="client">Client:</label>
            <select name="client_id" class="form-select" required>
                <option value="" disabled selected>-- Select --</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="transport" name="transport" value="1">
            <label class="form-check-label" for="transport" data-translate="withTransport">With transport</label>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-light"><tr>
                <th data-translate="product">Product</th>
                <th data-translate="priceunit">Unit Price</th>
                <th data-translate="quantity">Quantity</th>
                <th data-translate="subtotal">Subtotal</th>
            </tr></thead>
            <tbody>
                <?php foreach ($cartItems as $i): ?>
                <tr>
                    <td><?= e($i['name']) ?></td>
                    <td>€ <?= e(number_format($i['price'], 2, ',', '.')) ?></td>
                    <td><?= e($i['quantity']) ?></td>
                    <td>€ <?= e(number_format($i['subtotal'], 2, ',', '.')) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td><strong>€ <?= e(number_format($total, 2, ',', '.')) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <button type="submit" name="confirm_order" class="btn btn-success" data-translate="confirmOrder">Confirm Order</button>
        <a href="<?= url('/cart') ?>" class="btn btn-secondary">Back to Cart</a>
    </form>
</div>
