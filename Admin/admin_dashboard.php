<?php
include '../Common/db_connect.php';
session_start();

if ($_SESSION['role'] != 'Administrador') {
    header('Location: ../login.php');
    exit();
}

$query_users = "SELECT COUNT(*) AS total_users FROM usuarios";
$result_users = $conn->query($query_users);
$total_users = $result_users->fetch_assoc()['total_users'];

$query_approved_balance = "SELECT SUM(balance) AS total_balance FROM saldos WHERE status='approved'";
$result_approved_balance = $conn->query($query_approved_balance);
$total_balance = $result_approved_balance->fetch_assoc()['total_balance'];

$query_pending_balance = "SELECT SUM(balance) AS total_balance FROM saldos WHERE status='pending'";
$result_pending_balance = $conn->query($query_pending_balance);
$total_pending_balance = $result_pending_balance->fetch_assoc()['total_balance'];

$query_menus = "SELECT * FROM menus";
$result_menus = $conn->query($query_menus);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    <div class="dashboard-container">
        <h1>Dashboard del Administrador</h1>
        <div class="kpi">
            <div class="kpi-item">
                <h3>Usuarios Registrados</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="kpi-item">
                <h3>Saldo Aprobado</h3>
                <p>$<?php echo number_format($total_balance, 2); ?></p>
            </div>
            <div class="kpi-item">
                <h3>Saldo Pendiente de Aprobar</h3>
                <p>$<?php echo number_format($total_pending_balance, 2); ?></p>
            </div>
        </div>
        <h2>Menús Creados</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Fecha de Entrega</th>
                    <th>Precio</th>
                    <th>Fecha Límite de Compra</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($menu = $result_menus->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $menu['nombre']; ?></td>
                    <td><?php echo $menu['fecha_entrega']; ?></td>
                    <td>$<?php echo number_format($menu['precio'], 2); ?></td>
                    <td><?php echo $menu['fecha_limite_compra']; ?></td>
                    <td>
                        <button onclick="editMenu(<?php echo $menu['id']; ?>)">Editar</button>
                        <button onclick="deleteMenu(<?php echo $menu['id']; ?>)">Eliminar</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="../notifications_service.js"></script>
    <script>
        function editMenu(id) {
            // Implementar lógica de edición
        }
        function deleteMenu(id) {
            // Implementar lógica de eliminación
        }
    </script>
</body>
</html>
