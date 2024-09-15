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

// Filtrar por estado de saldo y agencia si están definidos
$filter_estado_saldo = isset($_GET['filter_estado_saldo']) ? $_GET['filter_estado_saldo'] : null;
$filter_agencia = isset($_GET['filter_agencia']) ? $_GET['filter_agencia'] : null;

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

// Aplicar los filtros si están presentes
$params = [$admin_id];
if ($filter_estado_saldo) {
    $query .= " AND p.estado_saldo = ?";
    $params[] = $filter_estado_saldo;
}
if ($filter_agencia) {
    $query .= " AND p.nombre_agencia = ?";
    $params[] = $filter_agencia;
}

$query .= " GROUP BY p.id";

// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener las agencias disponibles para el filtro
$agencias_query = "SELECT DISTINCT nombre_agencia FROM pedidos_hyt WHERE hyt_admin_id = ?";
$agencias_stmt = $pdo->prepare($agencias_query);
$agencias_stmt->execute([$admin_id]);
$agencias = $agencias_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales para los KPI (Pagado y Adeudado)
$kpi_query = "SELECT estado_saldo, SUM(d.cantidad * d.precio) AS total 
              FROM pedidos_hyt p 
              LEFT JOIN detalle_pedidos_hyt d ON p.id = d.pedido_id 
              WHERE p.hyt_admin_id = ?
              GROUP BY estado_saldo";
$kpi_stmt = $pdo->prepare($kpi_query);
$kpi_stmt->execute([$admin_id]);
$kpi_totals = $kpi_stmt->fetchAll(PDO::FETCH_ASSOC);

// Inicializar las variables de los KPI
$total_pagado = 0;
$total_adeudado = 0;

foreach ($kpi_totals as $kpi) {
    if ($kpi['estado_saldo'] == 'Pagado') {
        $total_pagado = $kpi['total'];
    } elseif ($kpi['estado_saldo'] == 'Adeudado') {
        $total_adeudado = $kpi['total'];
    }
}
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

    <!-- KPI Totales -->
    <div class="kpi-container">
        <div class="kpi">
            <h2>Total Adeudado</h2>
            <p><?php echo number_format($total_adeudado, 2); ?> ARS</p>
        </div>
        <div class="kpi">
            <h2>Total Pagado</h2>
            <p><?php echo number_format($total_pagado, 2); ?> ARS</p>
        </div>
    </div>

    <!-- Filtro por estado de saldo y agencia -->
    <div class="filter-container">
        <form method="GET" action="dashboard_hyt_admin.php">
            <label for="filter_estado_saldo">Filtrar por estado de saldo:</label>
            <select id="filter_estado_saldo" name="filter_estado_saldo">
                <option value="">Todos los estados</option>
                <option value="Pagado" <?php echo ($filter_estado_saldo == 'Pagado') ? 'selected' : ''; ?>>Pagado</option>
                <option value="Adeudado" <?php echo ($filter_estado_saldo == 'Adeudado') ? 'selected' : ''; ?>>Adeudado</option>
            </select>

            <label for="filter_agencia">Filtrar por agencia:</label>
            <select id="filter_agencia" name="filter_agencia">
                <option value="">Todas las agencias</option>
                <?php foreach ($agencias as $agencia): ?>
                    <option value="<?php echo htmlspecialchars($agencia['nombre_agencia']); ?>" 
                        <?php echo ($filter_agencia == $agencia['nombre_agencia']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($agencia['nombre_agencia']); ?>
                    </option>
                <?php endforeach; ?>
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
