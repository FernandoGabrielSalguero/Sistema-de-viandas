<?php
include '../common/header.php';

// Verificar si el usuario es un administrador
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener todos los pedidos
try {
    $stmt = $pdo->prepare("
        SELECT orders.id, parents.name as parent_name, children.name as child_name, menus.name as menu_name, orders.order_date
        FROM orders
        JOIN parents ON orders.parent_id = parents.id
        JOIN children ON orders.child_id = children.id
        JOIN menus ON orders.menu_id = menus.id
        ORDER BY orders.order_date DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $orders = [];
}

// Obtener todos los usuarios (padres)
try {
    $stmt = $pdo->prepare("SELECT * FROM parents ORDER BY name ASC");
    $stmt->execute();
    $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $parents = [];
}

// Obtener todos los menús
try {
    $stmt = $pdo->prepare("SELECT * FROM menus ORDER BY date DESC");
    $stmt->execute();
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $menus = [];
}
?>

<div class="container">
    <h2>Panel de Control del Administrador</h2>
    
    <div class="admin-section">
        <h3>Pedidos Recientes</h3>
        <table>
            <thead>
                <tr>
                    <th>Padre</th>
                    <th>Hijo</th>
                    <th>Menú</th>
                    <th>Fecha del Pedido</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['parent_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['child_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['menu_name']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="admin-section">
        <h3>Usuarios (Padres)</h3>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parents as $parent): ?>
                <tr>
                    <td><?php echo htmlspecialchars($parent['name']); ?></td>
                    <td><?php echo htmlspecialchars($parent['email']); ?></td>
                    <td><?php echo htmlspecialchars($parent['phone']); ?></td>
                    <td><?php echo number_format($parent['saldo'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="admin-section">
        <h3>Menús</h3>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $menu): ?>
                <tr>
                    <td><?php echo htmlspecialchars($menu['name']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($menu['date'])); ?></td>
                    <td><?php echo number_format($menu['price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.container {
    padding: 20px;
}
.admin-section {
    margin-bottom: 40px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table, th, td {
    border: 1px solid #ddd;
}
th, td {
    padding: 8px;
    text-align: left;
}
th {
    background-color: #f4f4f4;
}
</style>

<?php
include '../common/footer.php';
?>
