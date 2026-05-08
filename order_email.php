<?php
// order_email.php
function generateOrderEmail($translations, $client, $cart_items, $order_id, $total_amount, $currency, $company_name, $salesman) {
    ob_start(); // Inicia o buffer de saída
    ?>
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Order Confirmation</title>
        <style>
            /* CSS styles (como os definidos anteriormente) */
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='email-header'>
                <h1><?php echo translate('new_order', $translations) . " #" . $order_id; ?></h1>
            </div>
            <div class='email-body'>
                <h2><?php echo translate('clientDetails', $translations); ?></h2>
                <div class='client-info'>
                    <p><strong><?php echo translate('client_name', $translations); ?>:</strong> <?php echo htmlspecialchars($client['name']); ?></p>
                    <p><strong><?php echo translate('client_address', $translations); ?>:</strong> <?php echo htmlspecialchars($client['address']) . ", " . htmlspecialchars($client['city']) . ", " . htmlspecialchars($client['state']) . " " . htmlspecialchars($client['zip']); ?></p>
                    <p><strong><?php echo translate('client_email', $translations); ?>:</strong> <?php echo htmlspecialchars($client['email']); ?></p>
                    <p><strong><?php echo translate('client_phone', $translations); ?>:</strong> <?php echo htmlspecialchars($client['phone']); ?></p>
                </div>
                <h2><?php echo translate('order_details', $translations); ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th><?php echo translate('product', $translations); ?></th>
                            <th><?php echo translate('unit_price', $translations); ?></th>
                            <th><?php echo translate('quantity', $translations); ?></th>
                            <th><?php echo translate('subtotal', $translations); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($currency) . " " . number_format($item['price'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($currency) . " " . number_format($item['subtotal'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class='total-row'>
                            <td colspan='3' style='text-align:right;'><?php echo translate('total', $translations); ?>:</td>
                            <td><?php echo htmlspecialchars($currency) . " " . number_format($total_amount, 2, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
                <p><strong><?php echo translate('order_number', $translations); ?>:</strong> <?php echo $order_id; ?></p>
                <p><strong><?php echo translate('salesman', $translations); ?>:</strong> <?php echo htmlspecialchars($salesman); ?></p>
            </div>
            <div class='email-footer'>
                <p><?php echo htmlspecialchars($company_name) . " | " . translate('thank_you', $translations); ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean(); // Retorna o conteúdo do buffer
}
?>
