<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['filter_date'])) {
    $filter_date = $_POST['filter_date'];
    
    try {
        $stmt = $pdo->prepare("
            SELECT orders.id, parents.name as parent_name, children.name as child_name, menus.name as menu_name, orders.order_date
            FROM orders
            JOIN parents ON orders.parent_id = parents.id
            JOIN children ON orders.child_id = children.id
            JOIN menus ON orders.menu_id = menus.id
            WHERE orders.order_date = ?
            ORDER BY orders.order_date DESC
        ");
        $stmt->execute([$filter_date]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $html = '';
        foreach ($orders as $order) {
            $html .= "<tr>";
            $html .= "<td>" . htmlspecialchars($order['parent_name']) . "</td>";
            $html .= "<td>" . htmlspecialchars($order['child_name']) . "</td>";
            $html .= "<td>" . htmlspecialchars($order['menu_name']) . "</td>";
            $html .= "<td>" . date('d/m/Y', strtotime($order['order_date'])) . "</td>";
            $html .= "</tr>";
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) AS total_orders FROM orders WHERE order_date = ?");
        $stmt->execute([$filter_date]);
        $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

        echo json_encode(['html' => $html, 'total_orders' => $total_orders]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['html' => "<tr><td colspan='4'>Error al filtrar los pedidos.</td></tr>", 'total_orders' => 0]);
    }
} else {
    echo json_encode(['html' => "<tr><td colspan='4'>No se proporcionó una fecha de filtro válida.</td></tr>", 'total_orders' => 0]);
}