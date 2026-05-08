<?php
// order_pdf.php
include 'header.php';
include 'translations.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'You need to be logged in to view the order details.';
    header('Location: index.php');
    exit();
}

if (!isset($_GET['order_id'])) {
    $_SESSION['error_message'] = 'Invalid order.';
    header('Location: order_history.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];

// Carregar o autoload do Composer (ajusta o caminho se necessário)
require_once 'vendor/dompdf/vendor/autoload.php'; // ou, se tiveres feito download manual: require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$order_id = intval($_GET['order_id']);

// Obter os dados da encomenda, dependendo do tipo de utilizador
if ($role_id == 1) {
    // Administrador pode ver qualquer encomenda
    $stmt_order = $mysqli->prepare('
        SELECT o.*, c.name AS client_name
        FROM orders o
        INNER JOIN clients c ON o.client_id = c.id
        WHERE o.id = ?
    ');
    $stmt_order->bind_param('i', $order_id);
} else {
    // Utilizadores normais apenas podem ver as suas próprias encomendas
    $stmt_order = $mysqli->prepare('
        SELECT o.*, c.name AS client_name
        FROM orders o
        INNER JOIN clients c ON o.client_id = c.id
        WHERE o.id = ? AND o.user_id = ?
    ');
    $stmt_order->bind_param('ii', $order_id, $user_id);
}

$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc();
$stmt_order->close();

if (!$order) {
    $_SESSION['error_message'] = 'Order not found or you do not have permission to view it.';
    header('Location: order_history.php');
    exit();
}

// Obter os itens da encomenda
$stmt_items = $mysqli->prepare('
    SELECT oi.*, p.name 
    FROM order_items oi 
    INNER JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
');
$stmt_items->bind_param('i', $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$order_items = $result_items->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

// Fechar a ligação à base de dados
$mysqli->close();

// Preparar o HTML para o PDF
$html = '
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            margin: 40px;
            color: #333;
        }
        header {
            text-align: center;
            margin-bottom: 20px;
        }
        header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            color: #444;
        }
        header img {
            max-width: 150px;
        }
        .order-details {
            margin: 20px 0;
            font-size: 14px;
        }
        .order-details p {
            margin: 4px 0;
        }
        h2 {
            text-align: center;
            margin-top: 30px;
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        footer {
            text-align: center;
            font-size: 12px;
            color: #aaa;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <header>
        <!-- Substitui "logo.png" pelo caminho do teu logótipo -->
        <img src="logo.png" alt="Logo da Empresa">
        <h1>Detalhes da Encomenda #' . htmlspecialchars($order['id']) . '</h1>
    </header>
    <div class="order-details">
        <p><strong>Nome do Cliente:</strong> ' . htmlspecialchars($order['client_name']) . '</p>
        <p><strong>Data da Encomenda:</strong> ' . date("d/m/Y H:i", strtotime($order['created_at'])) . '</p>
        <p><strong>Valor Total:</strong> &euro; ' . number_format($order['total_amount'], 2, ',', '.') . '</p>
        <p><strong>Transporte:</strong> ' . ($order['transport'] == 1 ? "Com Transporte" : "Sem Transporte") . '</p>
    </div>
    <h2>Itens da Encomenda</h2>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Preço Unitário</th>
                <th>Quantidade</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>';
            foreach ($order_items as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $html .= '
            <tr>
                <td>' . htmlspecialchars($item['name']) . '</td>
                <td>&euro; ' . number_format($item['price'], 2, ',', '.') . '</td>
                <td>' . htmlspecialchars($item['quantity']) . '</td>
                <td>&euro; ' . number_format($subtotal, 2, ',', '.') . '</td>
            </tr>';
            }
$html .= '
        </tbody>
    </table>
    <footer>
        <p>Obrigado pela sua encomenda!</p>
    </footer>
</body>
</html>';


// Instanciar o Dompdf e carregar o HTML
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

// Definir o tamanho do papel e a orientação
$dompdf->setPaper('A4', 'portrait');

// Renderizar o HTML como PDF
$dompdf->render();

// Enviar o PDF gerado para o browser (inline)
$dompdf->stream("order_{$order['id']}.pdf", array("Attachment" => true));

exit();
?>
