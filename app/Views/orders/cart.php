<?php /** @var array $cartItems */ ?>
<div class="row">
    <h1 data-translate="shoppingCart">Shopping Cart</h1>

    <?php if (!empty($cartItems)): ?>
        <form method="POST" action="<?= url('/cart') ?>">
            <?= csrf_field() ?>
            <table class="table table-bordered table-striped">
                <thead class="table-light"><tr>
                    <th data-translate="product">Product</th>
                    <th data-translate="priceunit">Unit Price</th>
                    <th data-translate="quantity">Quantity</th>
                    <th data-translate="subtotal">Subtotal</th>
                    <th data-translate="actions">Actions</th>
                </tr></thead>
                <tbody>
                    <?php $total = 0; foreach ($cartItems as $i): $sub = $i['price'] * $i['quantity']; $total += $sub; ?>
                    <tr>
                        <td><?= e($i['name']) ?></td>
                        <td>€ <?= e(number_format($i['price'], 2, ',', '.')) ?></td>
                        <td><input type="number" name="quantities[<?= e($i['product_id']) ?>]" value="<?= e($i['quantity']) ?>" min="1" max="<?= e($i['stock']) ?>" class="form-control" required></td>
                        <td>€ <?= e(number_format($sub, 2, ',', '.')) ?></td>
                        <td>
                            <button type="submit" name="remove_from_cart" formnovalidate class="btn btn-danger"
                                    onclick="this.form.elements['product_id'].value=<?= e($i['product_id']) ?>;return confirm('Remove this product?')">Remove</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td colspan="2"><strong>€ <?= e(number_format($total, 2, ',', '.')) ?></strong></td>
                    </tr>
                </tbody>
            </table>
            <input type="hidden" name="product_id" value="">
            <div class="sticky-cta d-flex flex-wrap gap-2 justify-content-end mt-4">
                <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
                <a href="<?= url('/finalize-order') ?>" class="btn btn-success">Finalize Order</a>
            </div>
        </form>
    <?php else: ?>
        <p data-translate="emptyCart">Your cart is empty.</p>
    <?php endif; ?>

    <div class="mt-4">
        <a href="<?= url('/order-products') ?>" class="btn btn-secondary" data-translate="continueShopping">
            <i class="fas fa-arrow-left"></i> Continue Shopping
        </a>
    </div>
</div>
