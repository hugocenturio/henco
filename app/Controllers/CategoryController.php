<?php

namespace App\Controllers;

use App\Core\Controller;

class CategoryController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $db = $this->db();
        $errorMessage = null;

        if ($this->request->isPost()) {
            csrf_verify();
            if (isset($_POST['add_category'])) {
                $name = trim($_POST['name'] ?? '');
                $stmt = $db->prepare('INSERT INTO categories (name) VALUES (?)');
                $stmt->bind_param('s', $name); $stmt->execute(); $stmt->close();
            } elseif (isset($_POST['edit_category'])) {
                $id   = (int) ($_POST['category_id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $stmt = $db->prepare('UPDATE categories SET name = ? WHERE id = ?');
                $stmt->bind_param('si', $name, $id); $stmt->execute(); $stmt->close();
            } elseif (isset($_POST['delete_category'])) {
                $id = (int) ($_POST['category_id'] ?? 0);
                $stmt = $db->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
                $stmt->bind_param('i', $id); $stmt->execute();
                $stmt->bind_result($count); $stmt->fetch(); $stmt->close();
                if ($count > 0) {
                    $errorMessage = 'Cannot delete a category with associated products.';
                } else {
                    $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
                    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
                }
            }
            if ($errorMessage === null) $this->redirect('/categories');
        }

        $categories = $db->query('SELECT * FROM categories ORDER BY name ASC')->fetch_all(MYSQLI_ASSOC);
        $this->view('categories/index', compact('categories', 'errorMessage'), 'main', [
            'page_title' => 'Categories', 'current' => 'categories',
        ]);
    }
}
