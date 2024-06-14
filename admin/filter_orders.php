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

        foreach ($orders as $order) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($order['parent_name']) . "</td>";
            echo "<td>" . htmlspecialchars($order['child_name']) . "</td>";
            echo "<td>" . htmlspecialchars($order['menu_name']) . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($order['order_date'])) . "</td>";
            echo "</tr>";
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "<tr><td colspan='4'>Error al filtrar los pedidos.</td></tr>";
    }
} else {
    echo "<tr><td colspan='4'>No se proporcionó una fecha de filtro válida.</td></tr>";
}