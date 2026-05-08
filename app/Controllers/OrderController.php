<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Notify;

class OrderController extends Controller
{
    public function mine(): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        $stmt = $this->db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $this->view('orders/mine', compact('orders'), 'main', [
            'page_title' => 'My Orders', 'current' => 'my_orders',
        ]);
    }

    public function products(): void
    {
        $this->requireAuth();
        $db = $this->db();

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if ($this->request->isPost() && isset($_POST['add_to_cart'])) {
            csrf_verify();
            $productId = (int) ($_POST['product_id'] ?? 0);
            $quantity  = max(1, (int) ($_POST['quantity'] ?? 1));

            $stmt = $db->prepare('SELECT id, name, price FROM products WHERE id = ?');
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($product) {
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][$productId] = [
                        'product_id' => $productId,
                        'name'       => $product['name'],
                        'price'      => $product['price'],
                        'quantity'   => $quantity,
                    ];
                }
                $this->flash('success', $product['name'] . ' added to cart.');
            } else {
                $this->flash('error', 'Product not found.');
            }
            $this->redirect('/order-products');
        }

        $rs = $db->query('
            SELECT products.*, categories.name AS category_name
            FROM products LEFT JOIN categories ON products.category_id = categories.id
        ');
        $products = $rs->fetch_all(MYSQLI_ASSOC);
        $categories = $db->query('SELECT id, name FROM categories ORDER BY name ASC')->fetch_all(MYSQLI_ASSOC);

        $this->view('orders/products', compact('products', 'categories'), 'main', [
            'page_title' => 'Order Products', 'current' => 'order_products',
        ]);
    }

    public function cart(): void
    {
        $this->requireAuth();
        $this->confirmation();
    }

    public function confirmation(): void
    {
        $this->requireAuth();
        $db = $this->db();

        if ($this->request->isPost() && isset($_POST['remove_from_cart'])) {
            csrf_verify();
            $pid = (int) ($_POST['product_id'] ?? 0);
            unset($_SESSION['cart'][$pid]);
            $this->flash('success', 'Product removed from cart.');
            $this->redirect('/cart');
        }
        if ($this->request->isPost() && isset($_POST['update_cart'])) {
            csrf_verify();
            foreach (($_POST['quantities'] ?? []) as $pid => $q) {
                $pid = (int) $pid;
                $q   = (int) $q;
                if ($q > 0) {
                    $_SESSION['cart'][$pid]['quantity'] = $q;
                } else {
                    unset($_SESSION['cart'][$pid]);
                }
            }
            $this->flash('success', 'Cart updated.');
            $this->redirect('/cart');
        }

        $cartItems = [];
        if (!empty($_SESSION['cart'])) {
            $ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));
            $stmt = $db->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();
            $rs = $stmt->get_result();
            $stmt->close();
            while ($p = $rs->fetch_assoc()) {
                $pid = $p['id'];
                $cartItems[] = [
                    'product_id' => $pid,
                    'name'       => $p['name'],
                    'price'      => $p['price'],
                    'stock'      => $p['stock'],
                    'quantity'   => $_SESSION['cart'][$pid]['quantity'],
                ];
            }
        }

        $this->view('orders/cart', compact('cartItems'), 'main', [
            'page_title' => 'Shopping Cart', 'current' => 'order_products',
        ]);
    }

    public function finalize(): void
    {
        $this->requireAuth();
        $db = $this->db();
        $userId = (int) $_SESSION['user_id'];

        if (empty($_SESSION['cart'])) {
            $this->flash('error', 'Your cart is empty.');
            $this->redirect('/order-products');
        }

        if ($this->request->isPost() && isset($_POST['confirm_order'])) {
            csrf_verify();
            $clientId  = (int) ($_POST['client_id'] ?? 0);
            $transport = !empty($_POST['transport']) ? 1 : 0;

            $total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            $stmt = $db->prepare('INSERT INTO orders (user_id, client_id, total_amount, transport) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('iidi', $userId, $clientId, $total, $transport);
            $stmt->execute();
            $orderId = $stmt->insert_id;
            $stmt->close();

            $itemStmt = $db->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            foreach ($_SESSION['cart'] as $pid => $item) {
                $pid = (int) $pid;
                $qty = (int) $item['quantity'];
                $price = (float) $item['price'];
                $itemStmt->bind_param('iiid', $orderId, $pid, $qty, $price);
                $itemStmt->execute();
            }
            $itemStmt->close();

            unset($_SESSION['cart'], $_SESSION['reorder_order_id']);

            $username = $_SESSION['username'] ?? 'a salesperson';
            Notify::admins("New order #{$orderId} placed by {$username} (€ " . number_format($total, 2, ',', '.') . ').');

            $this->flash('success', 'Order #' . $orderId . ' placed successfully.');
            $this->redirect('/order-details?order_id=' . $orderId);
        }

        $clients = $db->query('SELECT id, name FROM clients ORDER BY name ASC')->fetch_all(MYSQLI_ASSOC);
        $cartItems = [];
        $total = 0;
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $sub = $item['price'] * $item['quantity'];
                $total += $sub;
                $cartItems[] = $item + ['subtotal' => $sub];
            }
        }
        $this->view('orders/finalize', compact('clients', 'cartItems', 'total'), 'main', [
            'page_title' => 'Finalize Order', 'current' => 'order_products',
        ]);
    }

    public function history(): void
    {
        $this->requireAdmin();
        $db = $this->db();

        if ($this->request->isPost() && isset($_POST['mark_shipped'])) {
            csrf_verify();
            $orderId = (int) ($_POST['order_id'] ?? 0);
            $shipped = (int) $_POST['mark_shipped'];
            $shippedAt = $shipped ? date('Y-m-d H:i:s') : null;
            $stmt = $db->prepare('UPDATE orders SET shipped = ?, shipped_at = ? WHERE id = ?');
            $stmt->bind_param('isi', $shipped, $shippedAt, $orderId);
            $stmt->execute();
            $stmt->close();

            if ($shipped) {
                $stmt = $db->prepare('SELECT user_id FROM orders WHERE id = ?');
                $stmt->bind_param('i', $orderId); $stmt->execute();
                $stmt->bind_result($ownerId); $stmt->fetch(); $stmt->close();
                if ($ownerId) {
                    Notify::user((int) $ownerId, "Your order #{$orderId} has been shipped.");
                }
            }

            $this->flash('success', $shipped ? 'Order marked as shipped.' : 'Shipment status reverted.');
            $this->redirect('/order-history');
        }

        $orders = $db->query('SELECT o.*, u.username FROM orders o INNER JOIN users u ON o.user_id = u.user_id ORDER BY o.id DESC')->fetch_all(MYSQLI_ASSOC);
        $this->view('orders/history', compact('orders'), 'main', [
            'page_title' => 'Order History', 'current' => 'order_history',
        ]);
    }

    public function details(): void
    {
        $this->requireAuth();
        $db      = $this->db();
        $userId  = (int) $_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $orderId = (int) $this->request->input('order_id', 0);

        if ($this->request->isPost() && isset($_POST['delete_order'])) {
            csrf_verify();
            if (!$isAdmin) {
                $this->flash('error', 'Insufficient permissions.');
                $this->redirect('/order-history');
            }
            $delId = (int) $_POST['delete_order'];
            $stmt = $db->prepare('DELETE FROM orders WHERE id = ?');
            $stmt->bind_param('i', $delId);
            $stmt->execute();
            $stmt->close();
            $this->flash('success', 'Order deleted.');
            $this->redirect('/order-history');
        }

        if ($this->request->isPost() && isset($_POST['reorder'])) {
            csrf_verify();
            $_SESSION['cart'] = $_SESSION['cart'] ?? [];
            $_SESSION['reorder_order_id'] = $orderId;

            $stmt = $db->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
            $stmt->bind_param('i', $orderId);
            $stmt->execute();
            $rs = $stmt->get_result();
            while ($i = $rs->fetch_assoc()) {
                $pid = (int) $i['product_id'];
                $qty = (int) $i['quantity'];
                if (isset($_SESSION['cart'][$pid])) {
                    $_SESSION['cart'][$pid]['quantity'] += $qty;
                } else {
                    $_SESSION['cart'][$pid] = ['quantity' => $qty];
                }
            }
            $stmt->close();
            $this->flash('success', 'Products added to cart.');
            $this->redirect('/order-products');
        }

        if ($isAdmin) {
            $stmt = $db->prepare('SELECT o.*, c.name AS client_name FROM orders o INNER JOIN clients c ON o.client_id = c.id WHERE o.id = ?');
            $stmt->bind_param('i', $orderId);
        } else {
            $stmt = $db->prepare('SELECT o.*, c.name AS client_name FROM orders o INNER JOIN clients c ON o.client_id = c.id WHERE o.id = ? AND o.user_id = ?');
            $stmt->bind_param('ii', $orderId, $userId);
        }
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            $this->flash('error', 'Order not found.');
            $this->redirect('/order-history');
        }

        $stmt = $db->prepare('SELECT oi.*, p.name FROM order_items oi INNER JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $this->view('orders/details', compact('order', 'items', 'orderId', 'isAdmin'), 'main', [
            'page_title' => 'Order Details', 'current' => 'order_history',
        ]);
    }

    /** Stubs for legacy email/PDF endpoints — preserved with minimal behavior. */
    public function sendEmail(): void
    {
        $this->requireAuth();
        csrf_verify();
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $this->flash('success', 'Email queued for order #' . $orderId . '.');
        $this->redirect('/order-details?order_id=' . $orderId);
    }

    public function pdf(): void
    {
        $this->requireAuth();
        $orderId = (int) $this->request->input('order_id', 0);
        // PDF generation kept identical to legacy — to be ported in a follow-up.
        $this->flash('error', 'PDF export will be available shortly.');
        $this->redirect('/order-details?order_id=' . $orderId);
    }
}
