<?php
include 'header.php';
include 'translations.php';
require_once 'helpers.php';

$company_name = $_SESSION['company_name'];
$page_title = $company_name . ' | ' . translate('cart', $translations);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_verify();

    // 1) Remove item from cart
    if (isset($_POST['remove_from_cart'])) {
        $prod_id = intval($_POST['remove_from_cart']);
        unset($_SESSION['cart'][$prod_id]);
        $_SESSION['success_message'] = translate('productRemoved', $translations);
        header('Location: cart.php');
        exit();
    }

    // 2) Update cart quantities
    if (isset($_POST['update_cart']) && isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $product_id = intval($product_id);
            $quantity   = intval($quantity);
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
        $_SESSION['success_message'] = translate('cartUpdated', $translations);
        header('Location: cart.php');
        exit();
    }

    // 3) Clear cart
    if (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
        $_SESSION['success_message'] = translate('cartCleared', $translations);
        header('Location: cart.php');
        exit();
    }

}

// Get product details in the cart
$cart_items = [];
if (!empty($_SESSION['cart'])) {
    $product_ids  = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $types        = str_repeat('i', count($product_ids));

    $stmt = $mysqli->prepare('SELECT * FROM products WHERE id IN (' . $placeholders . ')');
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    while ($product = $result->fetch_assoc()) {
        $product_id   = $product['id'];
        $cart_items[] = [
            'product_id' => $product_id,
            'name'       => $product['name'],
            'pricevat'   => $product['pricevat'],
            'stock'      => $product['stock'],
            'quantity'   => $_SESSION['cart'][$product_id]['quantity'],
        ];
    }
}

// 4) Save reorder — uses the existing $mysqli connection from dbconnect.php
if (isset($_POST['save_order'])) {
    csrf_verify();
    if (!empty($_SESSION['reorder_order_id'])) {
        $order_id_to_save = intval($_SESSION['reorder_order_id']);

        // Delete existing items and re-insert from the current cart
        $stmt_delete = $mysqli->prepare('DELETE FROM order_items WHERE order_id = ?');
        $stmt_delete->bind_param('i', $order_id_to_save);
        $stmt_delete->execute();
        $stmt_delete->close();

        $stmt_insert = $mysqli->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)'
        );
        foreach ($cart_items as $ci) {
            $stmt_insert->bind_param('iiid',
                $order_id_to_save,
                $ci['product_id'],
                $ci['quantity'],
                $ci['pricevat']
            );
            $stmt_insert->execute();
        }
        $stmt_insert->close();

        // Create notification
        $reorder_user_id      = (int)$_SESSION['user_id'];
        $reorder_notification = "Order #$order_id_to_save updated (reorder) by "
                                . htmlspecialchars($_SESSION['username'] ?? 'N/A');
        $stmt_notif = $mysqli->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
        $stmt_notif->bind_param('is', $reorder_user_id, $reorder_notification);
        $stmt_notif->execute();
        $stmt_notif->close();

        $_SESSION['success_message'] = translate('orderSaved', $translations);
        header('Location: cart.php');
        exit();
    } else {
        $_SESSION['error_message'] = translate('noOrderInSession', $translations);
        header('Location: cart.php');
        exit();
    }
}

$mysqli->close();
include 'template.php';
?>
<?php if (!empty($cart_items)): ?>
    <form method="POST" action="">
        <?php echo csrf_field(); ?>
        <div class="row">
            <div class="col-lg-6 col-md-6 mb-4">
                <h1 data-translate="checkout">Checkout</h1>
            </div>

            <div class="col-lg-6 col-md-6 mb-4 text-right">

                <button type="submit" name="clear_cart"
                        class="btn btn-danger mb-4 mr-2"
                        data-translate="clearCart">
                    Clear Cart
                </button>

                <button type="submit" name="update_cart"
                        class="btn btn-warning mb-4 mr-2"
                        data-translate="updateCart">
                    Update Cart
                </button>

                <a href="finalize_order.php"
                   class="btn btn-success mb-4 mr-2"
                   data-translate="finalizeOrder">
                    Finalize Order
                </a>

                <?php if (!empty($_SESSION['reorder_order_id'])): ?>
                    <button type="submit" name="save_order"
                            class="btn btn-info mb-4 mr-2"
                            data-translate="saveOrder">
                        Save Order
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <table id="Data_Table_1" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th data-translate="product">Product</th>
                        <th data-translate="priceunit">Unit Price</th>
                        <th data-translate="quantity">Quantity</th>
                        <th data-translate="subtotal">Subtotal</th>
                        <th data-translate="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ($cart_items as $item): ?>
                        <?php
                            $subtotal = $item['pricevat'] * $item['quantity'];
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>€ <?php echo htmlspecialchars(number_format($item['pricevat'], 2, ',', '.')); ?></td>
                            <td>
                                <input type="number"
                                       name="quantities[<?php echo $item['product_id']; ?>]"
                                       value="<?php echo $item['quantity']; ?>"
                                       min="1"
                                       max="<?php echo $item['stock']; ?>"
                                       class="form-control"
                                       required>
                            </td>
                            <td>€ <?php echo htmlspecialchars(number_format($subtotal, 2, ',', '.')); ?></td>
                            <td class="text-right">
                                <button type="submit"
                                        name="remove_from_cart"
                                        value="<?php echo $item['product_id']; ?>"
                                        class="btn btn-danger"
                                        data-translate="remove">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end" data-translate="totalWVAT">
                            <strong>Total w/VAT:</strong>
                        </td>
                        <td colspan="2">
                            <strong>€ <?php echo htmlspecialchars(number_format($total, 2, ',', '.')); ?></strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>

<?php else: ?>
    <div class="row">
        <p data-translate="emptyCart">The cart is empty.</p>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
