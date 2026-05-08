<?php

namespace App\Controllers;

use App\Core\Controller;
use DateTime;
use IntlDateFormatter;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userId  = (int) $_SESSION['user_id'];
        $isAdmin = !empty($_SESSION['is_admin']);
        $db      = $this->db();

        if ($this->request->isPost() && isset($_POST['clear_reorder'])) {
            csrf_verify();
            unset($_SESSION['reorder_order_id']);
            $this->redirect('/dashboard');
        }

        // Hydrate company_name once per session for chrome rendering.
        if (!isset($_SESSION['company_name'])) {
            $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
            $key  = 'company_name';
            $stmt->bind_param('s', $key);
            $stmt->execute();
            $stmt->bind_result($v);
            $stmt->fetch();
            $stmt->close();
            $_SESSION['company_name'] = $v ?: 'Default Company Name';
        }

        // Top stats
        if ($isAdmin) {
            $row = $db->query('SELECT COUNT(*) AS total_orders, SUM(total_amount) AS total_revenue FROM orders')->fetch_assoc();
        } else {
            $stmt = $db->prepare('SELECT COUNT(*) AS total_orders, SUM(total_amount) AS total_revenue FROM orders WHERE user_id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
        $totalOrders  = $row['total_orders']  ?? 0;
        $totalRevenue = $row['total_revenue'] ?? 0;

        $bestSelling = $this->bestSelling($isAdmin, $userId);
        $topUserOrders   = $isAdmin ? $this->oneRow('SELECT u.username, COUNT(o.id) AS total_orders FROM orders o INNER JOIN users u ON o.user_id = u.user_id GROUP BY o.user_id ORDER BY total_orders DESC LIMIT 1') : null;
        $topUserRevenue  = $isAdmin ? $this->oneRow('SELECT u.username, SUM(o.total_amount) AS total_revenue FROM orders o INNER JOIN users u ON o.user_id = u.user_id GROUP BY o.user_id ORDER BY total_revenue DESC LIMIT 1') : null;
        $topClientRevenue = $isAdmin ? $this->oneRow('SELECT c.name, SUM(o.total_amount) AS total_revenue FROM orders o INNER JOIN clients c ON o.client_id = c.id GROUP BY o.client_id ORDER BY total_revenue DESC LIMIT 1') : null;

        // Charts
        $ordersByMonth = $this->ordersByMonth($isAdmin, $userId);
        [$months, $orders] = $this->lastTwelveMonths($ordersByMonth);

        $topSellers = [];
        $sellerNames = [];
        $salesCounts = [];
        if ($isAdmin) {
            $rs = $db->query('SELECT u.username, COUNT(o.id) AS total_sales FROM users u INNER JOIN orders o ON u.user_id = o.user_id GROUP BY u.username ORDER BY total_sales DESC LIMIT 5');
            $topSellers = $rs->fetch_all(MYSQLI_ASSOC);
            foreach ($topSellers as $s) {
                $sellerNames[] = $s['username'];
                $salesCounts[] = $s['total_sales'];
            }
        }

        // Distinct clients by salesman
        $salesmanClientsData = [];
        $allUsernames        = [];
        $lastMonths          = $this->lastMonthsLabeled();
        if ($isAdmin) {
            $rs = $db->query("
                SELECT DATE_FORMAT(o.created_at, '%Y-%m') AS month_key, u.username,
                       COUNT(DISTINCT o.client_id) AS distinct_clients
                FROM orders o INNER JOIN users u ON o.user_id = u.user_id
                GROUP BY month_key, u.user_id
                ORDER BY month_key ASC
            ");
            while ($row = $rs->fetch_assoc()) {
                $u = $row['username'];
                $salesmanClientsData[$u][$row['month_key']] = (int) $row['distinct_clients'];
                $allUsernames[] = $u;
            }
            $allUsernames = array_values(array_unique($allUsernames));
            sort($allUsernames);
        }

        $this->view('dashboard/index', [
            'isAdmin'             => $isAdmin,
            'totalOrders'         => $totalOrders,
            'totalRevenue'        => $totalRevenue,
            'bestSelling'         => $bestSelling,
            'topUserOrders'       => $topUserOrders,
            'topUserRevenue'      => $topUserRevenue,
            'topClientRevenue'    => $topClientRevenue,
            'months'              => $months,
            'orders'              => $orders,
            'sellerNames'         => $sellerNames,
            'salesCounts'         => $salesCounts,
            'salesmanClientsData' => $salesmanClientsData,
            'allUsernames'        => $allUsernames,
            'lastMonths'          => $lastMonths,
        ], 'main', ['page_title' => 'Dashboard', 'current' => 'dashboard']);
    }

    private function bestSelling(bool $isAdmin, int $userId): ?array
    {
        if ($isAdmin) {
            $stmt = $this->db()->prepare(
                'SELECT p.name, SUM(oi.quantity) AS total_quantity
                 FROM order_items oi INNER JOIN products p ON oi.product_id = p.id
                 GROUP BY oi.product_id ORDER BY total_quantity DESC LIMIT 1'
            );
        } else {
            $stmt = $this->db()->prepare(
                'SELECT p.name, SUM(oi.quantity) AS total_quantity
                 FROM order_items oi
                 INNER JOIN products p ON oi.product_id = p.id
                 INNER JOIN orders o ON oi.order_id = o.id
                 WHERE o.user_id = ?
                 GROUP BY oi.product_id ORDER BY total_quantity DESC LIMIT 1'
            );
            $stmt->bind_param('i', $userId);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        return $row;
    }

    private function ordersByMonth(bool $isAdmin, int $userId): array
    {
        $sql = $isAdmin
            ? 'SELECT DATE_FORMAT(created_at, "%Y-%m") AS month, COUNT(*) AS total_orders FROM orders GROUP BY month ORDER BY month ASC'
            : 'SELECT DATE_FORMAT(created_at, "%Y-%m") AS month, COUNT(*) AS total_orders FROM orders WHERE user_id = ? GROUP BY month ORDER BY month ASC';
        $stmt = $this->db()->prepare($sql);
        if (!$isAdmin) {
            $stmt->bind_param('i', $userId);
        }
        $stmt->execute();
        $map = [];
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $map[$r['month']] = (int) $r['total_orders'];
        }
        $stmt->close();
        return $map;
    }

    private function lastTwelveMonths(array $map): array
    {
        $months = $orders = [];
        $today  = new DateTime();
        $hasIntl = class_exists('IntlDateFormatter');
        for ($i = 11; $i >= 0; $i--) {
            $dt = (clone $today)->modify("-$i month");
            $key = $dt->format('Y-m');
            if ($hasIntl) {
                $fmt = new IntlDateFormatter('pt_PT', IntlDateFormatter::LONG, IntlDateFormatter::NONE, null, null, 'LLLL yyyy');
                $months[] = ucfirst($fmt->format($dt));
            } else {
                $months[] = $dt->format('M Y');
            }
            $orders[] = $map[$key] ?? 0;
        }
        return [$months, $orders];
    }

    private function lastMonthsLabeled(): array
    {
        $list = [];
        $today = new DateTime();
        $hasIntl = class_exists('IntlDateFormatter');
        for ($i = 11; $i >= 0; $i--) {
            $dt = (clone $today)->modify("-$i month");
            $list[] = [
                'key'   => $dt->format('Y-m'),
                'label' => $hasIntl
                    ? ucfirst((new IntlDateFormatter('pt_PT', IntlDateFormatter::LONG, IntlDateFormatter::NONE, null, null, 'LLLL yyyy'))->format($dt))
                    : $dt->format('M Y'),
            ];
        }
        return $list;
    }

    private function oneRow(string $sql): ?array
    {
        $rs = $this->db()->query($sql);
        return $rs ? ($rs->fetch_assoc() ?: null) : null;
    }
}
