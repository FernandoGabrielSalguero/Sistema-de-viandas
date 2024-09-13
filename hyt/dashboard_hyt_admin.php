<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_admin') {
    header("Location: ../login.php");
    exit();
}

include 'header_hyt_admin.php';
include '../includes/db.php';

// Ejemplo de consulta para obtener los pedidos realizados por las agencias bajo la supervisión del hyt_admin
$admin_id = $_SESSION['usuario_id'];

$query = "SELECT p.id, p.nombre_agencia, p.fecha_pedido, p.estado, SUM(dp.almuerzo_caliente + dp.cena_caliente) as total_viandas
          FROM pedidos_hyt p
          LEFT JOIN detalle_pedidos_hyt dp ON p.id = dp.pedido_id
          WHERE p.hyt_admin_id = ? 
          GROUP BY p.id, p.nombre_agencia, p.fecha_pedido, p.estado";

$stmt = $pdo->prepare($query);
$stmt->execute([$admin_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard HYT Admin</title>
    <link rel="stylesheet" href="../css/hyt_variables.css">
</head>
<body>
    <h1>Pedidos de las agencias supervisadas</h1>

    <table>
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>Agencia</th>
                <th>Fecha de Pedido</th>
                <th>Estado</th>
                <th>Total Viandas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
            <tr>
                <td><?php echo htmlspecialchars($pedido['id']); ?></td>
                <td><?php echo htmlspecialchars($pedido['nombre_agencia']); ?></td>
                <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                <td><?php echo htmlspecialchars($pedido['estado']); ?></td>
                <td><?php echo htmlspecialchars($pedido['total_viandas']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
