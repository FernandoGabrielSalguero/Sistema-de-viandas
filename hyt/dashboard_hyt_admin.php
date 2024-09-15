<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_admin') {
    header("Location: ../login.php");
    exit();
}
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_hyt_admin.php';
include '../includes/db.php';

// Variables de filtros
$filter_saldo = isset($_GET['filter_saldo']) ? $_GET['filter_saldo'] : '';
$filter_agencia = isset($_GET['filter_agencia']) ? $_GET['filter_agencia'] : '';
$filter_destino = isset($_GET['filter_destino']) ? $_GET['filter_destino'] : '';
$filter_fecha_entrega = isset($_GET['filter_fecha_entrega']) ? $_GET['filter_fecha_entrega'] : '';

// Consulta SQL para obtener los pedidos
$query = "
    SELECT p.id, p.nombre_agencia, p.fecha_pedido, p.fecha_modificacion, p.interno, d.nombre as destino, p.fecha_salida,
           GROUP_CONCAT(CONCAT(dp.nombre, ' (', dp.cantidad, ')') SEPARATOR '<br>') AS productos,
           GROUP_CONCAT(dp.precio SEPARATOR '<br>') AS precios_unitarios,
           SUM(dp.cantidad * dp.precio) AS total, p.estado_saldo
    FROM pedidos_hyt p
    LEFT JOIN detalle_pedidos_hyt dp ON p.id = dp.pedido_id
    LEFT JOIN destinos_hyt d ON p.destino_id = d.id
    WHERE p.hyt_admin_id = :admin_id
";

// Aplicar filtros
$filters = [];
if ($filter_saldo !== '') {
    $query .= " AND p.estado_saldo = :filter_saldo";
    $filters['filter_saldo'] = $filter_saldo;
}
if ($filter_agencia !== '') {
    $query .= " AND p.nombre_agencia = :filter_agencia";
    $filters['filter_agencia'] = $filter_agencia;
}
if ($filter_destino !== '') {
    $query .= " AND d.nombre = :filter_destino";
    $filters['filter_destino'] = $filter_destino;
}
if ($filter_fecha_entrega !== '') {
    $query .= " AND p.fecha_salida = :filter_fecha_entrega";
    $filters['filter_fecha_entrega'] = $filter_fecha_entrega;
}

// Agrupar y ordenar resultados
$query .= "
    GROUP BY p.id
    ORDER BY p.id DESC
    LIMIT 20 OFFSET :offset";

// Preparar la consulta
$stmt = $pdo->prepare($query);
$filters['admin_id'] = $_SESSION['usuario_id'];
$stmt->execute($filters);

// Obtener los resultados
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener KPI (Totales de Adeudado y Pagado)
$kpi_query = "
    SELECT estado_saldo, SUM(dp.cantidad * dp.precio) as total
    FROM pedidos_hyt p
    LEFT JOIN detalle_pedidos_hyt dp ON p.id = dp.pedido_id
    WHERE p.hyt_admin_id = :admin_id
    GROUP BY p.estado_saldo
";
$kpi_stmt = $pdo->prepare($kpi_query);
$kpi_stmt->execute(['admin_id' => $_SESSION['usuario_id']]);
$kpi_totals = $kpi_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$kpi_adeudado = isset($kpi_totals['Adeudado']) ? $kpi_totals['Adeudado'] : 0;
$kpi_pagado = isset($kpi_totals['Pagado']) ? $kpi_totals['Pagado'] : 0;

// Obtener todas las agencias y destinos
$agencias_query = "SELECT DISTINCT nombre_agencia FROM pedidos_hyt WHERE hyt_admin_id = :admin_id";
$agencias_stmt = $pdo->prepare($agencias_query);
$agencias_stmt->execute(['admin_id' => $_SESSION['usuario_id']]);
$agencias = $agencias_stmt->fetchAll(PDO::FETCH_COLUMN);

$destinos_query = "SELECT DISTINCT d.nombre FROM destinos_hyt d JOIN pedidos_hyt p ON p.destino_id = d.id WHERE p.hyt_admin_id = :admin_id";
$destinos_stmt = $pdo->prepare($destinos_query);
$destinos_stmt->execute(['admin_id' => $_SESSION['usuario_id']]);
$destinos = $destinos_stmt->fetchAll(PDO::FETCH_COLUMN);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard HYT Admin</title>
    <link rel="stylesheet" href="../css/style_hyt_admin.css">
</head>
<body>
    <h1>Pedidos de las agencias supervisadas</h1>

    <div class="kpi-container">
        <div class="kpi">
            <h2>Total Adeudado</h2>
            <p><?php echo number_format($kpi_adeudado, 2, ',', '.'); ?> ARS</p>
        </div>
        <div class="kpi">
            <h2>Total Pagado</h2>
            <p><?php echo number_format($kpi_pagado, 2, ',', '.'); ?> ARS</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-container">
        <form method="GET" action="dashboard_hyt_admin.php">
            <label for="filter_saldo">Filtrar por estado de saldo:</label>
            <select id="filter_saldo" name="filter_saldo">
                <option value="">Todos</option>
                <option value="Adeudado" <?php if ($filter_saldo === 'Adeudado') echo 'selected'; ?>>Adeudado</option>
                <option value="Pagado" <?php if ($filter_saldo === 'Pagado') echo 'selected'; ?>>Pagado</option>
            </select>

            <label for="filter_agencia">Filtrar por agencia:</label>
            <select id="filter_agencia" name="filter_agencia">
                <option value="">Todas las agencias</option>
                <?php foreach ($agencias as $agencia): ?>
                    <option value="<?php echo htmlspecialchars($agencia); ?>" <?php if ($filter_agencia === $agencia) echo 'selected'; ?>><?php echo htmlspecialchars($agencia); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="filter_destino">Filtrar por destino:</label>
            <select id="filter_destino" name="filter_destino">
                <option value="">Todos los destinos</option>
                <?php foreach ($destinos as $destino): ?>
                    <option value="<?php echo htmlspecialchars($destino); ?>" <?php if ($filter_destino === $destino) echo 'selected'; ?>><?php echo htmlspecialchars($destino); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="filter_fecha_entrega">Filtrar por fecha de entrega:</label>
            <input type="date" id="filter_fecha_entrega" name="filter_fecha_entrega" value="<?php echo htmlspecialchars($filter_fecha_entrega); ?>">

            <button type="submit">Filtrar</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>Agencia</th>
                <th>Fecha Pedido</th>
                <th>Fecha Modificación</th>
                <th>Interno</th>
                <th>Destino</th>
                <th>Fecha de Entrega</th>
                <th>Productos (Cantidades)</th>
                <th>Precio Unitario</th>
                <th>Total</th>
                <th>Estado de Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
                <tr class="<?php echo strtolower($pedido['estado_saldo']); ?>">
                    <td><?php echo htmlspecialchars($pedido['id']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['nombre_agencia']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['fecha_modificacion']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['interno']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['destino']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['fecha_salida']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['productos']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['precios_unitarios']); ?> ARS</td>
                    <td><?php echo number_format($pedido['total'], 2, ',', '.'); ?> ARS</td>
                    <td><?php echo htmlspecialchars($pedido['estado_saldo']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
