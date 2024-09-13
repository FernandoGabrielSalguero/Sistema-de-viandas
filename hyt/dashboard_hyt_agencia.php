<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_agencia') {
    header("Location: ../login.php");
    exit();
}

include 'header_hyt_agencia.php';
include '../includes/db.php';

// Obtener los pedidos del usuario hyt_agencia actual
$agencia_id = $_SESSION['usuario_id'];

$query = "SELECT p.id, p.fecha_pedido, p.estado, SUM(dp.almuerzo_caliente + dp.cena_caliente) as total_viandas
          FROM pedidos_hyt p
          LEFT JOIN detalle_pedidos_hyt dp ON p.id = dp.pedido_id
          WHERE p.agencia_id = ? 
          GROUP BY p.id, p.fecha_pedido, p.estado";

$stmt = $pdo->prepare($query);
$stmt->execute([$agencia_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard HYT Agencia</title>
    <link rel="stylesheet" href="../css/hyt_variables.css">
</head>
<body>
    <h1>Tus Pedidos de Viandas</h1>

    <table>
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>Fecha de Pedido</th>
                <th>Estado</th>
                <th>Total Viandas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
            <tr>
                <td><?php echo htmlspecialchars($pedido['id']); ?></td>
                <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                <td><?php echo htmlspecialchars($pedido['estado']); ?></td>
                <td><?php echo htmlspecialchars($pedido['total_viandas']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="crear_pedido.php" class="button">Crear nuevo pedido</a>
</body>
</html>
