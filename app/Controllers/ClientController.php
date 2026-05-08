<?php

namespace App\Controllers;

use App\Core\Controller;

class ClientController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $db = $this->db();

        if ($this->request->isPost()) {
            csrf_verify();
            if (isset($_POST['add_client'])) {
                $stmt = $db->prepare(
                    'INSERT INTO clients (name, nif, email, phone, address, city, state, zip)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $vals = array_map(fn ($k) => trim($_POST[$k] ?? ''), ['name','nif','email','phone','address','city','state','zip']);
                $stmt->bind_param('ssssssss', ...$vals);
                $stmt->execute(); $stmt->close();
            } elseif (isset($_POST['edit_client'])) {
                $id = (int) ($_POST['client_id'] ?? 0);
                $stmt = $db->prepare(
                    'UPDATE clients SET name=?, nif=?, email=?, phone=?, address=?, city=?, state=?, zip=? WHERE id=?'
                );
                $vals = array_map(fn ($k) => trim($_POST[$k] ?? ''), ['name','nif','email','phone','address','city','state','zip']);
                $vals[] = $id;
                $stmt->bind_param('ssssssssi', ...$vals);
                $stmt->execute(); $stmt->close();
            } elseif (isset($_POST['delete_client'])) {
                $id = (int) ($_POST['client_id'] ?? 0);
                $stmt = $db->prepare('DELETE FROM clients WHERE id = ?');
                $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
            }
            $this->redirect('/clients');
        }

        $clients = $db->query('SELECT * FROM clients ORDER BY name ASC')->fetch_all(MYSQLI_ASSOC);
        $this->view('clients/index', compact('clients'), 'main', [
            'page_title' => 'Clients', 'current' => 'clients',
        ]);
    }

    public function details(): void
    {
        $this->requireAdmin();
        $clientId = (int) $this->request->input('client_id', 0);
        $db = $this->db();

        $stmt = $db->prepare('SELECT * FROM clients WHERE id = ?');
        $stmt->bind_param('i', $clientId); $stmt->execute();
        $client = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $db->prepare('SELECT * FROM orders WHERE client_id = ? ORDER BY id DESC');
        $stmt->bind_param('i', $clientId); $stmt->execute();
        $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $this->view('clients/details', compact('client', 'orders'), 'main', [
            'page_title' => 'Client Details', 'current' => 'clients',
        ]);
    }

    public function apiDetails(): void
    {
        $this->requireAuth();
        $clientId = (int) $this->request->input('client_id', 0);
        $db = $this->db();
        $stmt = $db->prepare('SELECT * FROM clients WHERE id = ?');
        $stmt->bind_param('i', $clientId); $stmt->execute();
        $client = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $this->json(['client' => $client]);
    }
}
