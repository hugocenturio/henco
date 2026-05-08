<?php

namespace App\Controllers;

use App\Core\Controller;

class ProductController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $db = $this->db();

        if ($this->request->isPost()) {
            csrf_verify();
            if (isset($_POST['add_product'])) {
                $stmt = $db->prepare(
                    'INSERT INTO products (name, reference, description, price, pricevat, stock, category_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $name        = trim($_POST['name'] ?? '');
                $reference   = trim($_POST['reference'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $price       = (float) ($_POST['price'] ?? 0);
                $pricevat    = (float) ($_POST['pricevat'] ?? 0);
                $stock       = (int)   ($_POST['stock'] ?? 0);
                $categoryId  = (int)   ($_POST['category_id'] ?? 0);
                $stmt->bind_param('sssddii', $name, $reference, $description, $price, $pricevat, $stock, $categoryId);
                $stmt->execute();
                $stmt->close();
            } elseif (isset($_POST['edit_product'])) {
                $stmt = $db->prepare(
                    'UPDATE products SET name = ?, reference = ?, description = ?, price = ?, pricevat = ?, stock = ?, category_id = ? WHERE id = ?'
                );
                $name = trim($_POST['name'] ?? '');
                $reference = trim($_POST['reference'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $price = (float) ($_POST['price'] ?? 0);
                $pricevat = (float) ($_POST['pricevat'] ?? 0);
                $stock = (int) ($_POST['stock'] ?? 0);
                $categoryId = (int) ($_POST['category_id'] ?? 0);
                $productId = (int) ($_POST['product_id'] ?? 0);
                $stmt->bind_param('sssddiii', $name, $reference, $description, $price, $pricevat, $stock, $categoryId, $productId);
                $stmt->execute();
                $stmt->close();
            } elseif (isset($_POST['delete_product'])) {
                $productId = (int) ($_POST['product_id'] ?? 0);
                $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
                $stmt->bind_param('i', $productId);
                $stmt->execute();
                $stmt->close();
            }
            $this->redirect('/products');
        }

        $products = $db->query(
            'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC'
        )->fetch_all(MYSQLI_ASSOC);
        $categories = $db->query('SELECT * FROM categories')->fetch_all(MYSQLI_ASSOC);

        $this->view('products/index', compact('products', 'categories'), 'main', [
            'page_title' => 'Products', 'current' => 'products',
        ]);
    }

    public function details(): void
    {
        $this->requireAdmin();
        $db = $this->db();
        $productId = (int) $this->request->input('product_id', 0);

        if ($this->request->isPost()) {
            csrf_verify();
            if (isset($_POST['edit_product'])) {
                $stmt = $db->prepare('UPDATE products SET name = ?, reference = ?, description = ?, price = ?, pricevat = ?, stock = ?, category_id = ? WHERE id = ?');
                $name = trim($_POST['name'] ?? '');
                $reference = trim($_POST['reference'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $price = (float) ($_POST['price'] ?? 0);
                $pricevat = (float) ($_POST['pricevat'] ?? 0);
                $stock = (int) ($_POST['stock'] ?? 0);
                $categoryId = (int) ($_POST['category_id'] ?? 0);
                $pid = (int) ($_POST['product_id'] ?? 0);
                $stmt->bind_param('sssddiii', $name, $reference, $description, $price, $pricevat, $stock, $categoryId, $pid);
                $stmt->execute();
                $stmt->close();
                $productId = $pid;
            }
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(__DIR__, 2) . '/uploads/product_images/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $name = basename($_FILES['product_image']['name']);
                $path = 'uploads/product_images/' . time() . '_' . $name;
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], dirname(__DIR__, 2) . '/' . $path)) {
                    $stmt = $db->prepare('INSERT INTO product_images (product_id, image_path) VALUES (?, ?)');
                    $stmt->bind_param('is', $productId, $path);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $db->prepare('SELECT * FROM product_images WHERE product_id = ?');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $categories = $db->query('SELECT id, name FROM categories ORDER BY name ASC')->fetch_all(MYSQLI_ASSOC);

        $this->view('products/details', compact('product', 'images', 'categories'), 'main', [
            'page_title' => 'Product Details', 'current' => 'products',
        ]);
    }

    public function upload(): void
    {
        $this->requireAdmin();
        $db = $this->db();
        $message = '';

        if ($this->request->isPost() && isset($_FILES['csv_file'])) {
            csrf_verify();
            $tmp = $_FILES['csv_file']['tmp_name'];
            if (($handle = fopen($tmp, 'r')) !== false) {
                $header = fgetcsv($handle, 1000, ';');
                $required = ['name', 'reference', 'description', 'price', 'pricevat', 'stock', 'category_id'];
                $missing = array_diff($required, $header ?? []);
                if (!empty($missing)) {
                    $message = 'Missing required fields: ' . implode(', ', $missing);
                } else {
                    $idx = array_flip($header);
                    $inserted = 0; $skipped = 0;
                    while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                        if (count($data) < count($header)) { $skipped++; continue; }
                        $name = $data[$idx['name']];
                        $ref  = $data[$idx['reference']];
                        $desc = $data[$idx['description']];
                        $price    = (float) $data[$idx['price']];
                        $pricevat = (float) $data[$idx['pricevat']];
                        $stock    = (int)   $data[$idx['stock']];
                        $catId    = (int)   $data[$idx['category_id']];

                        $stmt = $db->prepare('SELECT id FROM categories WHERE id = ?');
                        $stmt->bind_param('i', $catId);
                        $stmt->execute(); $stmt->store_result();
                        if ($stmt->num_rows === 0) { $stmt->close(); $skipped++; continue; }
                        $stmt->close();

                        $stmt = $db->prepare('SELECT id FROM products WHERE reference = ?');
                        $stmt->bind_param('s', $ref);
                        $stmt->execute(); $stmt->store_result();
                        if ($stmt->num_rows > 0) { $stmt->close(); $skipped++; continue; }
                        $stmt->close();

                        $stmt = $db->prepare('INSERT INTO products (name, reference, description, price, pricevat, stock, category_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
                        $stmt->bind_param('sssddii', $name, $ref, $desc, $price, $pricevat, $stock, $catId);
                        if ($stmt->execute()) $inserted++; else $skipped++;
                        $stmt->close();
                    }
                    fclose($handle);
                    $message = "$inserted products imported, $skipped skipped.";
                }
            } else {
                $message = 'Could not open CSV file.';
            }
        }

        $this->view('products/upload', compact('message'), 'main', [
            'page_title' => 'Import Products', 'current' => 'upload',
        ]);
    }
}
