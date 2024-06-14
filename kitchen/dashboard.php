<?php
include '../common/header.php';

// Verificar si el usuario tiene rol de cocina
if ($_SESSION['role'] !== 'kitchen') {
    header("Location: ../login.php");
    exit;
}

// Obtener los pedidos realizados, organizados por colegio, curso y vianda
try {
    $stmt = $pdo->prepare("
        SELECT orders.id, schools.name as school_name, courses.name as course_name, menus.name as menu_name, menus.date as menu_date, COUNT(orders.id) as total_orders
        FROM orders
        JOIN children ON orders.child_id = children.id
        JOIN courses ON children.course_id = courses.id
        JOIN schools ON courses.school_id = schools.id
        JOIN menus ON orders.menu_id = menus.id
        WHERE menus.date >= CURDATE()
        GROUP BY schools.name, courses.name, menus.name, menus.date
        ORDER BY menus.date, schools.name, courses.name
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $orders = [];
}

// Agrupar pedidos por fecha y colegio
$grouped_orders = [];
foreach ($orders as $order) {
    $grouped_orders[$order['menu_date']][$order['school_name']][] = $order;
}
?>

<div class="container">
    <h2>Dashboard de Cocina</h2>

    <?php foreach ($grouped_orders as $date => $schools): ?>
        <div class="date-section">
            <h3><?php echo date('d/m/Y', strtotime($date)); ?></h3>
            <?php foreach ($schools as $school_name => $orders): ?>
                <div class="school-section">
                    <h4><?php echo htmlspecialchars($school_name); ?></h4>
                    <div class="kpi-container">
                        <?php foreach ($orders as $order): ?>
                            <div class="kpi-card">
                                <h5><?php echo htmlspecialchars($order['course_name']); ?></h5>
                                <p><?php echo htmlspecialchars($order['menu_name']); ?></p>
                                <p>Pedidos: <?php echo $order['total_orders']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<style>
.container {
    padding: 20px;
}

.date-section {
    margin-bottom: 40px;
}

.school-section {
    margin-bottom: 20px;
    background-color: #f9f9f9;
    padding: 10px;
    border-radius: 5px;
}

.kpi-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.kpi-card {
    background-color: #ff0000;
    color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    width: calc(33.333% - 20px);
}

.kpi-card h5 {
    margin: 0;
    font-size: 18px;
}

.kpi-card p {
    margin: 5px 0 0;
    font-size: 16px;
}
</style>

<?php
include '../common/footer.php';
?>
