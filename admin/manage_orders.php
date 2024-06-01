<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

// Fetch orders from the database
$query = "SELECT orders.id, orders.status, orders.date, orders.child_id, orders.menu_id, 
                 children.name AS child_name, menu.name AS menu_name 
          FROM orders 
          JOIN children ON orders.child_id = children.id 
          JOIN menu ON orders.menu_id = menu.id";
$result = $conn->query($query);

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    header("Location: manage_orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Pedidos</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_users.php">Gestionar Usuarios</a>
        <a href="manage_menu.php">Gestionar Menú</a>
        <a href="manage_orders.php">Gestionar Pedidos</a>
        <a href="../logout.php">Cerrar Sesión</a>
    </div>
    <div class="main-content">
        <h2>Gestionar Pedidos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Niño</th>
                    <th>Vianda</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['date'] ?></td>
                        <td><?= $row['child_name'] ?></td>
                        <td><?= $row['menu_name'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td>
                            <form action="manage_orders.php" method="POST" style="display: inline-block;">
                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                <select name="status">
                                    <option value="Procesando" <?= $row['status'] == 'Procesando' ? 'selected' : '' ?>>Procesando</option>
                                    <option value="Aprobado" <?= $row['status'] == 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                                    <option value="Cancelado" <?= $row['status'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                                <button type="submit">Actualizar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
