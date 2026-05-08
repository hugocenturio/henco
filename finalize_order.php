<?php

/***************************************
 * Inclui ficheiros essenciais
 ***************************************/
include 'header.php';
include 'translations.php';
require_once 'helpers.php';

require 'vendor/autoload.php'; // Inclui a biblioteca Mailjet

use Mailjet\Client;
use Mailjet\Resources;
/***************************************
 * Verifica se $mysqli está definido (em header.php)
 * e inicializa variáveis da sessão
 ***************************************/
$company_name = isset($_SESSION['company_name']) ? $_SESSION['company_name'] : '';
// Define um array de traduções global a usar
$translations = [];

// Carrega as traduções consoante o locale da sessão
if (!empty($_SESSION['locale'])) {
    $translation_file = "locales/{$_SESSION['locale']}.json";
    if (file_exists($translation_file)) {
        $translations = json_decode(file_get_contents($translation_file), true) ?? [];
    }
}

// Define o título da página (depende da função translate() de translations.php)
$page_title = $company_name . ' | ' . translate('finalizeOrder', $translations);

/***************************************
 * Inicializa array do carrinho e total
 ***************************************/
$cart_items = [];
$total_amount = 0.0;
$order_id = 0;

/***************************************
 * Se existir carrinho, vai buscar 
 * dados à BD (nome, price) e 
 * calcula subtotais
 ***************************************/
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $quantity = $item['quantity'];

        // Preparar a query
        $stmt = $mysqli->prepare('SELECT name, pricevat FROM products WHERE id = ?');
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $stmt->bind_result($name, $pricevat);
        $stmt->fetch();
        $stmt->close();

        $subtotal = $pricevat * $quantity;
        $total_amount += $subtotal;

        $cart_items[] = [
            'product_id' => $product_id,
            'name'       => $name,
            'price'   => $pricevat,
            'quantity'   => $quantity,
            'subtotal'   => $subtotal
        ];
    }
}

/***************************************
 * Processamento do "finalizar encomenda"
 ***************************************/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['process_order']) || isset($_POST['save_order']))) {

    csrf_verify();

    // Verifica se foi selecionado cliente
    if (empty($_POST['client_id'])) {
        $_SESSION['error_message'] = 'Please select a client before proceeding.';
        header('Location: finalize_order.php');
        exit();
    }

    // Lê dados do formulário
    $client_id     = intval($_POST['client_id']);
    $client_address = trim($_POST['address']);
    $client_city    = trim($_POST['city']);
    $client_state   = trim($_POST['state']);
    $client_zip     = trim($_POST['zip']);

    // Verifica se campos de morada estão preenchidos
    if ($client_address === '' || $client_city === '' || 
        $client_state === ''  || $client_zip === '') {
        $_SESSION['error_message'] = 'All address fields are required.';
        header('Location: finalize_order.php');
        exit();
    }

    // Atualiza a morada do cliente
    $stmt_update_client = $mysqli->prepare('UPDATE clients SET address = ?, city = ?, state = ?, zip = ? WHERE id = ?');
    $stmt_update_client->bind_param('ssssi', $client_address, $client_city, $client_state, $client_zip, $client_id);
    $stmt_update_client->execute();
    $stmt_update_client->close();
        
    //Discount
    // Lê o valor do desconto em % (exemplo)
$discount = 0;
if (isset($_POST['discount'])) {
    // Converte para float
    $discount = floatval($_POST['discount']);
}

// Valida se está entre 0 e 100, se quiseres
if ($discount < 0 || $discount > 100) {
    $_SESSION['error_message'] = 'Invalid discount value.';
    header('Location: finalize_order.php');
    exit();
}    
        
        

    // Inicia transação
    $mysqli->begin_transaction();

    try {
        // Verifica o utilizador que está a finalizar
        $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
        if ($user_id === 0) {
            throw new Exception('Invalid user session.'); 
        }

        // Vai buscar dados atualizados do cliente
        $stmt_client = $mysqli->prepare('SELECT * FROM clients WHERE id = ?');
        $stmt_client->bind_param('i', $client_id);
        $stmt_client->execute();
        $client_result = $stmt_client->get_result();
        $client = $client_result->fetch_assoc();
        $stmt_client->close();

        if (!$client) {
            throw new Exception('Client not found.');
        }

        // Lê a opção de transporte (radio buttons)
        $transport = isset($_POST['transport']) ? intval($_POST['transport']) : 0;

 $discounted_total = $total_amount * (1 - $discount / 100);               
   // 1) Inserir na tabela orders
    $stmt_order = $mysqli->prepare('INSERT INTO orders (user_id, client_id, total_amount, discount) VALUES (?, ?, ?, ?)');
    $stmt_order->bind_param('iidd', $user_id, $client_id, $discounted_total, $discount);

    if (!$stmt_order->execute()) {
        throw new Exception('Falha ao criar order: ' . $stmt_order->error);
    }

    // 2) Obter o ID recém-criado
    $order_id = $stmt_order->insert_id;
    if ($order_id <= 0) {
        throw new Exception('order_id inválido');
    }

    // 3) Inserir na tabela order_items
    foreach ($cart_items as $item) {
        $stmt_item = $mysqli->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?,?)');
        $stmt_item->bind_param('iiid', $order_id, $item['product_id'], $item['quantity'],$item['price']);

        if (!$stmt_item->execute()) {
            throw new Exception('Falha ao inserir item: ' . $stmt_item->error);
        }
    }
            
    
            
    foreach ($cart_items as $item) {        
            
           $stmt_update_stock = $mysqli->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
           $stmt_update_stock->bind_param('ii', $item['quantity'], $item['product_id']);
           $stmt_update_stock->execute();
           $stmt_update_stock->close(); 
            
     }        

    
            
    $mysqli->commit();

// Create notification for both process_order and save_order
$notification_message = isset($_POST['process_order'])
    ? "New order #$order_id placed by " . htmlspecialchars($client['name'])
    : "Order #$order_id saved (draft) by " . htmlspecialchars($_SESSION['username'] ?? 'N/A');
$stmt_notification = $mysqli->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
$stmt_notification->bind_param('is', $user_id, $notification_message);
$stmt_notification->execute();
$stmt_notification->close();

if (isset($_POST['process_order'])) {

        // Busca email do manager e moeda nas definições
        $manager_email = get_setting($mysqli, 'manager_email');
        if (empty($manager_email)) {
            throw new Exception('Manager email is not set in the system settings.');
        }
        $currency = get_setting($mysqli, 'currency', '€');

        /***************************************
         * Prepara assunto e mensagem do email
         ***************************************/
        $subject = translate('new_order', $translations) . " #$order_id | " . htmlspecialchars($client['name']);

        // Construir corpo do email (HTML)
        $message  = "<h3>" . translate('new_order_placed', $translations) . "</h3>";
        $message .= "<p><strong>" . translate('client_name', $translations) . ":</strong> " . 
                     htmlspecialchars($client['name']) . "<br>";
        $message .= "<strong>" . translate('client_address', $translations) . ":</strong> " . 
                     htmlspecialchars($client['address']) . ", " . htmlspecialchars($client['city']) . ", " . 
                     htmlspecialchars($client['state']) . " " . htmlspecialchars($client['zip']) . "<br>";
        $message .= "<strong>" . translate('client_email', $translations) . ":</strong> " . 
                     htmlspecialchars($client['email']) . "<br>";
        $message .= "<strong>" . translate('client_phone', $translations) . ":</strong> " . 
                     htmlspecialchars($client['phone']) . "</p>";

        // Mostrar se tem transporte ou não
        $message .= "<p><strong>" . translate('transportOption', $translations) . ":</strong> " .
                    ($transport ? translate('withTransport', $translations) : translate('withoutTransport', $translations)) .
                    "</p>";

        $message .= "<h4>" . translate('order_details', $translations) . "</h4>";
        $message .= "<table border='1' cellpadding='5' cellspacing='0'>";
        $message .= "<thead><tr>
                       <th>" . translate('product', $translations) . "</th>
                       <th>" . translate('unit_price', $translations) . "</th>
                       <th>" . translate('quantity', $translations) . "</th>
                       <th>" . translate('subtotal', $translations) . "</th>
                     </tr></thead>";
        $message .= "<tbody>";

        // Adiciona linhas da encomenda
        foreach ($cart_items as $item) {
            $item_subtotal = $item['quantity'] * $item['price'];
            $message .= "<tr>";
            $message .= "<td>" . htmlspecialchars($item['name']) . "</td>";
            $message .= "<td>" . htmlspecialchars($currency) . " " .
                         number_format($item['price'], 2, ',', '.') . "</td>";
            $message .= "<td>" . htmlspecialchars($item['quantity']) . "</td>";
            $message .= "<td>" . htmlspecialchars($currency) . " " .
                         number_format($item_subtotal, 2, ',', '.') . "</td>";
            $message .= "</tr>";
        }
        // Linha de total
        $message .= "<tr>
                       <td colspan='3' style='text-align:right;'><strong>" . translate('total', $translations) . ":</strong></td>
                       <td><strong>" . htmlspecialchars($currency) . " " .
                             number_format($discounted_total, 2, ',', '.') . "</strong></td>
                     </tr>";
        $message .= "</tbody></table>";
 		$message .= "<p><strong>" . translate('discount', $translations) . ":</strong> $discount%<br>";
            
            
        $message .= "<p><strong>" . translate('order_number', $translations) . ":</strong> $order_id<br>";
        $message .= "<strong>" . translate('salesman', $translations) . ":</strong> " .
                    htmlspecialchars($_SESSION['username'] ?? 'N/A') . "</p>";

        // Busca email de envio (o "From")
        $send_email = get_setting($mysqli, 'send_email');
        if (empty($send_email) || !filter_var($send_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception(translate('invalid_email', $translations));
        }

try {
    if (empty(MAILJET_API_KEY) || empty(MAILJET_API_SECRET)) {
        throw new Exception('Mailjet API credentials are not configured.');
    }

    $mailjet = new Client(MAILJET_API_KEY, MAILJET_API_SECRET, true, ['version' => 'v3.1']);

    // Configura o email
    $email = [
        'Messages' => [
            [
                'From' => [
                    'Email' => $send_email, // Email de envio
                    'Name'  => $company_name // Nome do remetente
                ],
                'To' => [
                    [
                        'Email' => $manager_email, // Email do destinatário
                        'Name'  => $_SESSION['username'] // Nome do destinatário
                    ]
                ],
                'Subject' => $subject,
                'TextPart' => strip_tags($message),
                'HTMLPart' => $message
            ]
        ]
    ];

    // Envia o email
    $response = $mailjet->post(Resources::$Email, ['body' => $email]);

    // Verifica o envio
    if (!$response->success()) {
        throw new Exception(translate('email_send_failed', $translations) . ': ' . $response->getReasonPhrase());
    }
} catch (Exception $e) {
    throw new Exception(translate('email_send_failed', $translations) . ': ' . $e->getMessage());
}

        // Limpa carrinho
        $_SESSION['cart'] = [];
} 
        // Faz commit
        $mysqli->commit();

$message = '';            
if (isset($_POST['process_order'])) {
$message = translate('orderSuccess', $translations);         
}else{
$message = translate('orderSaved', $translations); 
}
                     
            
        // Mensagem de sucesso e redireciona
        $_SESSION['success_message'] = $message. " #$order_id";
        unset($_SESSION['reorder_order_id']);    
        header('Location: dashboard.php');
        exit();

    } catch (Exception $e) {
        // Em caso de erro, faz rollback
        $mysqli->rollback();
        $_SESSION['error_message'] = translate('orderError', $translations) . $e->getMessage();
        header('Location: finalize_order.php');
        exit();
    }
}

// Carrega lista de clientes para o dropdown
$result_clients = $mysqli->query('SELECT id, name FROM clients');
$clients = $result_clients->fetch_all(MYSQLI_ASSOC);

include 'template.php';


$mysqli->close();
?>


<div class="row">
    <div class="col-lg-6 col-md-6 mb-4">  
        <h1 data-translate="finalizeOrder">Finalize Order</h1>
    </div>

    <div class="col-lg-6 col-md-6 mb-4 text-right">
        <form method="POST" action="">
            <?php echo csrf_field(); ?>
            <a href="cart.php" class="btn btn-secondary mt-3 mr-2" data-translate="backToCart">
                <i class="fas fa-arrow-left"></i> Back to Cart
            </a>

            <?php if (!empty($cart_items)): ?>
                <button 
                    class="btn btn-success mt-3 mr-2" 
                    type="submit" 
                    name="process_order" 
                    data-bs-dismiss="process" 
                    data-translate="processOrder">
                    Process Order
                </button>
            <?php endif; ?>
            <?php if (empty($_SESSION['reorder_order_id'])): ?>    
            <button 
                    class="btn btn-info mt-3" 
                    type="submit" 
                    name="save_order"
                    data-translate="saveOrder"
                >
                    Save Order
             </button>    
             <?php endif; ?>   
    </div>

    <div class="row">
        <!-- Selecção do cliente -->
        <div class="mb-4">
            <label for="clientSelect" class="form-label" data-translate="selectClient">
                Select Client:
            </label>
            <select id="clientSelect" name="client_id" class="form-select form-select-lg mb-3" required>
                <option value="" data-translate="chooseClient">Choose a client</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo htmlspecialchars($client['id']); ?>">
                        <?php echo htmlspecialchars($client['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Morada do cliente (editável) -->
        <div id="clientDetails" style="display: none;">
            <div class="mb-3">
                <label for="address" class="form-label" data-translate="address">Address:</label>
                <input type="text" id="address" name="address" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="city" class="form-label" data-translate="city">City:</label>
                <input type="text" id="city" name="city" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="state" class="form-label" data-translate="state">State:</label>
                <input type="text" id="state" name="state" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="zip" class="form-label" data-translate="zip">ZIP:</label>
                <input type="text" id="zip" name="zip" class="form-control" required>
            </div>
        </div>

        <!-- Opção de transporte -->
        <div class="mb-3">
            <label class="form-label" data-translate="transportOption">Transport Option</label>
            <div class="form-check">
                <input type="radio" id="withTransport" name="transport" value="1" class="form-check-input" required>
                <label for="withTransport" class="form-check-label" data-translate="withTransport">With Transport</label>
            </div>
            <div class="form-check">
                <input type="radio" id="withoutTransport" name="transport" value="0" class="form-check-input" required>
                <label for="withoutTransport" class="form-check-label" data-translate="withoutTransport">Without Transport</label>
            </div>
        </div>

        <!-- Campo de desconto -->
<div class="mt-4">
    <label for="discountField" class="form-label" data-translate="commercialDiscount">
        Commercial Discount (%)
    </label>
    <div class="input-group">
        <input 
            type="number"
            id="discountField"
            name="discount"   
            class="form-control"
            min="0"
            max="100"
            step="0.01"
            placeholder="Enter discount"
        />
    </div>
</div>

        <!-- Resumo da encomenda -->
        <h4 class="mt-4" data-translate="orderSummary">Order Summary</h4>
        <div class="table-responsive">
            <table id="Data_Table2" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th data-translate="product">Product</th>
                        <th data-translate="priceunit">Unit Price</th>
                        <th data-translate="quantity">Quantity</th>
                        <th data-translate="subtotal">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>€ <?php echo number_format($item['price'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>€ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end" data-translate="totalWVAT"><strong>Original Total w/VAT:</strong></td>
                        <td id="originalTotal" data-original-total="<?php echo $total_amount; ?>">
                            <strong>€ <?php echo number_format($total_amount, 2, ',', '.'); ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total w/Discount:</strong></td>
                        <td id="totalWithDiscount">
                            <strong>€ <?php echo number_format($total_amount, 2, ',', '.'); ?></strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- JS para atualizar o desconto -->
<script>
document.getElementById('discountField').addEventListener('input', function () {
    const discountField = document.getElementById('discountField');
    const originalTotalElement = document.getElementById('originalTotal');
    const totalWithDiscountElement = document.getElementById('totalWithDiscount');

    const discount = parseFloat(discountField.value) || 0;
    const originalTotal = parseFloat(originalTotalElement.dataset.originalTotal);

    if (discount < 0 || discount > 100) {
        totalWithDiscountElement.innerHTML = '<strong>Invalid Discount</strong>';
        return;
    }

    const discountedTotal = originalTotal * (1 - discount / 100);
    // Usa replace('.', ',') para notação PT se preferires
    totalWithDiscountElement.innerHTML = `<strong>${discountedTotal.toFixed(2).replace('.', ',')}</strong>`;
});
</script>

<!-- JS para lidar com a seleção do cliente (mostrar/ocultar morada, etc.) -->
<script src="js/selectClient.js"></script>
