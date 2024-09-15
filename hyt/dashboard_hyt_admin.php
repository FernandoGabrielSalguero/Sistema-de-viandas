<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_admin') {
    header("Location: ../login.php");
    exit();
}

include '../includes/header_hyt_admin.php';
include '../includes/db.php';

// Obtener el ID del administrador logueado
$admin_id = $_SESSION['usuario_id'];

// Filtrar por estado de saldo si está definido
$filter_estado_saldo = isset($_GET['filter_estado_saldo']) ? $_GET['filter_estado_saldo'] : null;

// Construir la consulta principal
$query = "SELECT p.id, p.nombre_agencia, p.fecha_pedido, p.fecha_modificacion, p.estado_saldo,
                 GROUP_CONCAT(d.nombre SEPARATOR ', ') AS productos, 
                 GROUP_CONCAT(d.cantidad SEPARATOR ', ') AS cantidades, 
                 GROUP_CONCAT(d.precio SEPARATOR ', ') AS precios_unitarios, 
                 SUM(d.cantidad * d.precio) AS total
          FROM pedidos_hyt p
          LEFT JOIN detalle_pedidos_hyt d ON p.id = d.pedido_id
          WHERE p.hyt_admin_id = ?
          ";

// Si se aplica un filtro por estado de saldo, agregarlo a la consulta
if ($filter_estado_saldo) {
    $query .= " AND p.estado_saldo = ?";
}

$query .= " GROUP BY p.id";

// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($query);
if ($filter_estado_saldo) {
    $stmt->execute([$admin_id, $filter_estado_saldo]);
} else {
    $stmt->execute([$admin_id]);
}
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard HYT Admin</title>
    <link rel="stylesheet" href="../css/style_dashboard_hyt_admin.css">
</head>
<body>
    <h1>Pedidos de las agencias supervisadas</h1>

    <!-- Filtro por estado de saldo -->
    <div class="filter-container">
        <form method="GET" action="dashboard_hyt_admin.php">
            <label for="filter_estado_saldo">Filtrar por estado de saldo:</label>
            <select id="filter_estado_saldo" name="filter_estado_saldo">
                <option value="">Todos los estados</option>
                <option value="Pagado" <?php echo ($filter_estado_saldo == 'Pagado') ? 'selected' : ''; ?>>Pagado</option>
                <option value="Adeudado" <?php echo ($filter_estado_saldo == 'Adeudado') ? 'selected' : ''; ?>>Adeudado</option>
            </select>
            <button type="submit">Filtrar</button>
        </form>
    </div>

    <!-- Tabla de pedidos -->
    <table class="estado-saldo-table">
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>Agencia</th>
                <th>Fecha Pedido</th>
                <th>Fecha Modificación</th>
                <th>Productos (Cantidades)</th>
                <th>Precio Unitario</th>
                <th>Total</th>
                <th>Estado de Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
            <tr>
                <td><?php echo htmlspecialchars($pedido['id']); ?></td>
                <td><?php echo htmlspecialchars($pedido['nombre_agencia']); ?></td>
                <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                <td><?php echo htmlspecialchars($pedido['fecha_modificacion']); ?></td>
                <td>
                    <?php 
                    $productos = explode(", ", $pedido['productos']);
                    $cantidades = explode(", ", $pedido['cantidades']);
                    foreach ($productos as $index => $producto) {
                        echo htmlspecialchars($producto) . " (" . htmlspecialchars($cantidades[$index]) . ")<br>";
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    $precios_unitarios = explode(", ", $pedido['precios_unitarios']);
                    foreach ($precios_unitarios as $precio_unitario) {
                        echo htmlspecialchars(number_format($precio_unitario, 2)) . " ARS<br>";
                    }
                    ?>
                </td>
                <td><?php echo number_format($pedido['total'], 2); ?> ARS</td>
                <td><?php echo htmlspecialchars($pedido['estado_saldo']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
