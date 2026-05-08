<?php
include 'header.php';
include 'translations.php';

// Iniciar sessão e verificar se o usuário está autenticado
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'You need to be logged in to view the order details.';
    header('Location: index.php'); // Redireciona para a página de login
    exit();
}

// Verifica se o parâmetro 'order_id' foi passado corretamente
if (!isset($_GET['order_id'])) {
    $_SESSION['error_message'] = 'Invalid order.';
    header('Location: order_history.php');
    exit();
}

// Variáveis de sessão
$user_id   = $_SESSION['user_id'];
$role_id   = $_SESSION['role_id'];
$company_name = isset($_SESSION['company_name']) ? $_SESSION['company_name'] : '';

// Define título da página
$page_title = $company_name . ' | ' . translate('order_details', $translations);

// Conexão com o banco de dados
require_once 'config/config.php';
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_error) {
    die('Database connection error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// 1) Lê o pedido de apagar a encomenda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    // Só administradores podem apagar (ajusta se quiseres outra regra)
    if ($role_id != 1) {
        $_SESSION['error_message'] = translate('noPermissions', $translations);//'You do not have permission to delete orders.';
        header('Location: order_history.php');
        exit();
    }

    $delete_order_id = intval($_POST['delete_order']);
    $stmt_delete = $mysqli->prepare("DELETE FROM orders WHERE id = ?");
    $stmt_delete->bind_param("i", $delete_order_id);
    $stmt_delete->execute();
    $affected = $stmt_delete->affected_rows;
    $stmt_delete->close();

    if ($affected > 0) {
        $_SESSION['success_message'] = translate('orderDeleted', $translations);//'Order deleted successfully.';
    } else {
        $_SESSION['error_message'] = translate('orderDeletedFailed', $translations);//'Failed to delete the order (not found or already deleted).';
    }
    header('Location: order_history.php');
    exit();
}


$order_id = intval($_GET['order_id']);


if ($role_id == 1) {
    // O usuário é administrador - pode ver qualquer encomenda
    $stmt_order = $mysqli->prepare('
        SELECT o.*, c.name AS client_name
        FROM orders o
        INNER JOIN clients c ON o.client_id = c.id
        WHERE o.id = ?
    ');
    $stmt_order->bind_param('i', $order_id);
} else {
    // O usuário não é administrador - pode ver apenas as suas próprias encomendas
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
    $_SESSION['error_message'] = translate('noPermissions', $translations);//'Order not found or you do not have permission to view it.';
    header('Location: order_history.php');
    exit();
}

// Obtém os itens da encomenda
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

// Fechar a conexão
$mysqli->close();


// 1) Reencomendar (adicionar produtos ao carrinho)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reorder'])) {

  // Define a variável de sessão com o ID da encomenda
    $_SESSION['reorder_order_id'] = $order_id;   
        
        if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
        
    // Percorre cada produto da encomenda e adiciona ao carrinho
    foreach ($order_items as $item) {
        $pid = $item['product_id'];
        $qty = intval($item['quantity']);

        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid]['quantity'] += $qty;
        } else {
            $_SESSION['cart'][$pid] = [
                'quantity' => $qty
            ];
        }
    }
    $_SESSION['success_message'] = translate('productsAddedToCart',$translations);
    header('Location: order_products.php');
    exit();
}

include 'template.php';
?>

<div class="row">
    <div class="col-md-12">
   
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-4" data-translate="order_details">
                Order Details #<?php echo htmlspecialchars($order['id']); ?>
            </h1>
            
  
            <div class="d-flex">
                    
                  <!-- Botão para reencomendar (reorder) -->
                <form method="POST" action="" class="me-2">
                    <button type="submit" name="reorder" class="btn btn-info" data-translate="reorder">
                        Reorder
                    </button>
                </form>                  

 
                <!-- Button to resend email -->
                <form method="POST" action="sendEmail.php" class="me-2">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <button type="submit" class="btn btn-primary">
                        <?php echo translate('sendOrderEmail', $translations) ?: 'Resend Email'; ?>
                    </button>
                </form>

                <?php if ($role_id == 1): ?>
                    <!-- Botão para apagar a encomenda (só mostra se for admin) -->
                    <form method="POST" action="">
                        <input type="hidden" name="delete_order" value="<?php echo $order['id']; ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i>
                            <?php echo translate('delete', $translations) ?: 'Delete Order'; ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <p><strong data-translate="client_name">Client Name:</strong> 
            <?php echo htmlspecialchars($order['client_name']); ?>
        </p>
        <p><strong data-translate="orderDate">Order Date:</strong> 
            <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))); ?>
        </p>
        <p><strong data-translate="totalValue">Total Amount:</strong> &euro; 
            <?php echo htmlspecialchars(number_format($order['total_amount'], 2, ',', '.')); ?>
        </p>
        <p>
            <strong data-translate="transport">Transport:</strong>
            <?php 
                echo $order['transport'] == 1 
                     ? translate('withTransport', $translations) 
                     : translate('withoutTransport', $translations);
            ?>
        </p>

        <h4 class="mt-4" data-translate="orderItems">Order Items</h4>
        <div class="table-responsive">
            <table 
                id="Data_Table_4"
                class="table table-striped table-bordered zero-configuration dataTable table-hover"
            >
                <thead>
                    <tr>
                        <th data-translate="product">Product</th>
                        <th data-translate="priceunit">Unit Price</th>
                        <th data-translate="quantity">Quantity</th>
                        <th data-translate="subtotal">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <?php $subtotal = $item['price'] * $item['quantity']; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>&euro; 
                                <?php echo htmlspecialchars(number_format($item['price'], 2, ',', '.')); ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>&euro; 
                                <?php echo htmlspecialchars(number_format($subtotal, 2, ',', '.')); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
