<?php
include '../common/header.php';

// Verificar si el usuario tiene rol de cocina
if ($_SESSION['role'] !== 'kitchen') {
    header("Location: ../login.php");
    exit;
}

// Obtener los pedidos realizados, organizados por colegio y curso
try {
    $stmt = $pdo->prepare("
        SELECT orders.id, schools.name as school_name, courses.name as course_name, menus.name as menu_name, menus.date as menu_date, parents.name as parent_name, children.name as child_name
        FROM orders
        JOIN children ON orders.child_id = children.id
        JOIN parents ON orders.parent_id = parents.id
        JOIN courses ON children.course_id = courses.id
        JOIN schools ON courses.school_id = schools.id
        JOIN menus ON orders.menu_id = menus.id
        ORDER BY schools.name, courses.name, menus.date
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $orders = [];
}
?>

<div class="container">
    <h2>Dashboard de Cocina</h2>

    <div class="kitchen-section">
        <h3>Pedidos Realizados</h3>
        <table>
            <thead>
                <tr>
                    <th>Colegio</th>
                    <th>Curso</th>
                    <th>Menú</th>
                    <th>Fecha</th>
                    <th>Padre</th>
                    <th>Hijo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['school_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['menu_name']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($order['menu_date'])); ?></td>
                    <td><?php echo htmlspecialchars($order['parent_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['child_name']); ?></td>
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

.kitchen-section {
    margin-bottom: 40px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table thead {
    background-color: #ff0000;
    color: white;
}

table th, table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #f1f1f1;
}

table th {
    font-weight: bold;
}
</style>

<?php
include '../common/footer.php';
?>
