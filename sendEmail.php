<?php
session_start();

// Inclui os ficheiros que inicializam a DB, traduções, etc.
include 'header.php';
include 'translations.php';
require_once 'helpers.php';

require 'vendor/autoload.php'; // Inclui a biblioteca Mailjet

use Mailjet\Client;
use Mailjet\Resources;

$company_name = isset($_SESSION['company_name']) ? $_SESSION['company_name'] : '';

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'You need to be logged in to send emails.';
    header('Location: index.php');
    exit();
}

// Verifica se recebemos o order_id (podes receber via GET ou POST, ajusta conforme o teu caso)
if (isset($_POST['order_id'])) {
    csrf_verify();
    $order_id = intval($_POST['order_id']);
} elseif (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
} else {
    $_SESSION['error_message'] = 'No order specified.';
    header('Location: order_history.php');
    exit();
}

// $mysqli is already available from dbconnect.php (included via header.php)

// 1) Carrega os dados da encomenda e do cliente
$stmt_order = $mysqli->prepare('
    SELECT o.*,
           c.name     AS client_name,
           c.address  AS client_address,
           c.city     AS client_city,
           c.state    AS client_state,
           c.zip      AS client_zip,
           c.email    AS client_email,
           c.phone    AS client_phone
    FROM orders o
    INNER JOIN clients c ON o.client_id = c.id
    WHERE o.id = ?
');
$stmt_order->bind_param('i', $order_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc();
$stmt_order->close();

if (!$order) {
    $_SESSION['error_message'] = 'Order not found.';
    header('Location: order_history.php');
    exit();
}

// Authorization: only the order owner or an admin can resend the email
$is_admin = isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
if (!$is_admin && $order['user_id'] !== (int)$_SESSION['user_id']) {
    $_SESSION['error_message'] = 'You are not authorized to perform this action.';
    header('Location: order_history.php');
    exit();
}

// 2) Carrega os itens da encomenda
$stmt_items = $mysqli->prepare('
    SELECT oi.product_id, oi.quantity, oi.price,
           p.name AS product_name
    FROM order_items oi
    INNER JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
');
$stmt_items->bind_param('i', $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

$cart_items = [];
$total_amount = 0.0;

while ($row = $result_items->fetch_assoc()) {
    $quantity   = $row['quantity'];
    $price      = $row['price'];
    $subtotal   = $quantity * $price;
    $total_amount += $subtotal;

    $cart_items[] = [
        'product_id' => $row['product_id'],
        'name'       => $row['product_name'],
        'price'      => $price,
        'quantity'   => $quantity,
        'subtotal'   => $subtotal
    ];
}
$stmt_items->close();

// Do "orders" já tens: $order['discount'], $order['transport'], etc. 
// Se usas colunas separadas, podes ir buscar discount, transport, etc.
// Exemplo:
$discount         = floatval($order['discount'] ?? 0);      // se a tabela "orders" tiver um campo "discount"
$transport        = intval($order['transport'] ?? 0);       // se a tabela "orders" tiver "transport" (0 ou 1)
$client_name      = $order['client_name'];
$discounted_total = $total_amount * (1 - $discount / 100);

// 3) Buscar settings como manager_email, currency, send_email
$manager_email = get_setting($mysqli, 'manager_email', 'manager@example.com');
$currency      = get_setting($mysqli, 'currency', '€');
$send_email    = get_setting($mysqli, 'send_email', 'henco@fungiware.com');

// 4) Construir o corpo do email (exatamente como no finalize_order.php)
$subject = translate('new_order', $translations) . " #$order_id | " . htmlspecialchars($client_name);

// Cabeçalho HTML
$message  = "<h3>" . translate('new_order_placed', $translations) . "</h3>";
$message .= "<p><strong>" . translate('client_name', $translations) . ":</strong> " . 
             htmlspecialchars($client_name) . "<br>";
$message .= "<strong>" . translate('client_address', $translations) . ":</strong> " . 
             htmlspecialchars($order['client_address']) . ", " . 
             htmlspecialchars($order['client_city'])    . ", " . 
             htmlspecialchars($order['client_state'])   . " " . 
             htmlspecialchars($order['client_zip'])     . "<br>";
$message .= "<strong>" . translate('client_email', $translations) . ":</strong> " . 
             htmlspecialchars($order['client_email']) . "<br>";
$message .= "<strong>" . translate('client_phone', $translations) . ":</strong> " . 
             htmlspecialchars($order['client_phone']) . "</p>";

// Transporte
$message .= "<p><strong>" . translate('transportOption', $translations) . ":</strong> ";
$message .= ($transport == 1) 
    ? translate('withTransport', $translations) 
    : translate('withoutTransport', $translations);
$message .= "</p>";

// Tabela de itens
$message .= "<h4>" . translate('order_details', $translations) . "</h4>";
$message .= "<table border='1' cellpadding='5' cellspacing='0'>";
$message .= "<thead>
               <tr>
                 <th>" . translate('product', $translations) . "</th>
                 <th>" . translate('unit_price', $translations) . "</th>
                 <th>" . translate('quantity', $translations) . "</th>
                 <th>" . translate('subtotal', $translations) . "</th>
               </tr>
             </thead>";
$message .= "<tbody>";

foreach ($cart_items as $item) {
    $message .= "<tr>";
    $message .= "<td>" . htmlspecialchars($item['name']) . "</td>";
    $message .= "<td>" . htmlspecialchars($currency) . " " . 
                 number_format($item['price'], 2, ',', '.') . "</td>";
    $message .= "<td>" . intval($item['quantity']) . "</td>";
    $message .= "<td>" . htmlspecialchars($currency) . " " .
                 number_format($item['subtotal'], 2, ',', '.') . "</td>";
    $message .= "</tr>";
}
// Linha de total
$message .= "<tr>
               <td colspan='3' style='text-align:right;'>
                 <strong>" . translate('total', $translations) . ":</strong>
               </td>
               <td>
                 <strong>" . htmlspecialchars($currency) . " " .
                   number_format($discounted_total, 2, ',', '.') . "</strong>
               </td>
             </tr>";
$message .= "</tbody></table>";

// Discount e order_number
$message .= "<p><strong>" . translate('discount', $translations) . ":</strong> " . 
            number_format($discount, 2) . "%<br>";
$message .= "<strong>" . translate('order_number', $translations) . ":</strong> $order_id<br>";
$message .= "<strong>" . translate('salesman', $translations) . ":</strong> " .
            htmlspecialchars($_SESSION['username'] ?? 'N/A') . "</p>";

// 5) Enviar email via Mailjet
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

$message = translate('orderSuccess', $translations);  
$_SESSION['success_message'] = $message. " #$order_id";

// Fecha a ligação
$mysqli->close();

// Redireciona para uma página (dashboard, order_details, etc.)
// Ajusta conforme a tua lógica. 
header("Location: order_details.php?order_id=$order_id");
exit;
?>