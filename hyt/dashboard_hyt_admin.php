<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_admin') {
    header("Location: ../login.php");
    exit();
}

include '../includes/header_hyt_admin.php';
include '../includes/db.php';

// Función para formatear las fechas
function formatearFecha($fecha) {
    return date('d-m-Y', strtotime($fecha));
}

// Valores por defecto para filtros
$filter_estado_saldo = $_GET['filter_estado_saldo'] ?? 'Todos';
$filter_agencia = $_GET['filter_agencia'] ?? 'Todas las agencias';
$filter_destino = $_GET['filter_destino'] ?? 'Todos los destinos';
$filter_fecha_entrega = $_GET['filter_fecha_entrega'] ?? '';

// Construir la consulta base
$query = "SELECT p.id, p.nombre_agencia, p.fecha_pedido, p.fecha_modificacion, p.interno, d.nombre as destino_nombre, p.fecha_salida, p.estado_saldo, 
          GROUP_CONCAT(CONCAT(dp.nombre, ' (', dp.cantidad, ')') SEPARATOR ', ') as productos, 
          GROUP_CONCAT(CONCAT(ROUND(dp.precio, 2)) SEPARATOR ', ') as precios,
          SUM(dp.cantidad * dp.precio) as total 
          FROM pedidos_hyt p
          LEFT JOIN detalle_pedidos_hyt dp ON p.id = dp.pedido_id
          LEFT JOIN destinos_hyt d ON p.destino_id = d.id
          WHERE p.hyt_admin_id = ?";

// Agregar condiciones de filtros
$conditions = [];
$params = [$_SESSION['usuario_id']];

if ($filter_estado_saldo != 'Todos') {
    $conditions[] = "p.estado_saldo = ?";
    $params[] = $filter_estado_saldo;
}

if ($filter_agencia != 'Todas las agencias') {
    $conditions[] = "p.nombre_agencia = ?";
    $params[] = $filter_agencia;
}

if ($filter_destino != 'Todos los destinos') {
    $conditions[] = "d.nombre = ?";
    $params[] = $filter_destino;
}

if (!empty($filter_fecha_entrega)) {
    $conditions[] = "p.fecha_salida = ?";
    $params[] = $filter_fecha_entrega;
}

// Si hay filtros, agréguelos a la consulta
if (count($conditions) > 0) {
    $query .= " AND " . implode(" AND ", $conditions);
}

// Continuar con el GROUP BY y ORDER BY
$query .= " GROUP BY p.id ORDER BY p.id DESC LIMIT 20";

// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener totales
$total_adeudado = 0;
$total_pagado = 0;
foreach ($pedidos as $pedido) {
    if ($pedido['estado_saldo'] == 'Adeudado') {
        $total_adeudado += $pedido['total'];
    } else {
        $total_pagado += $pedido['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos de las agencias supervisadas</title>
    <link rel="stylesheet" href="../css/style_dashboard_hyt_admin.css">
</head>
<body>
    <h1>Pedidos de las agencias supervisadas</h1>

    <div class="kpi-container">
        <div class="kpi">
            <h2>Total Adeudado</h2>
            <p><?php echo number_format($total_adeudado, 2, ',', '.'); ?> ARS</p>
        </div>
        <div class="kpi">
            <h2>Total Pagado</h2>
            <p><?php echo number_format($total_pagado, 2, ',', '.'); ?> ARS</p>
        </div>
    </div>

    <div class="filter-container">
        <form method="GET" action="dashboard_hyt_admin.php">
            <label for="filter_estado_saldo">Filtrar por estado de saldo:</label>
            <select id="filter_estado_saldo" name="filter_estado_saldo">
                <option value="Todos" <?php echo ($filter_estado_saldo == 'Todos') ? 'selected' : ''; ?>>Todos</option>
                <option value="Pagado" <?php echo ($filter_estado_saldo == 'Pagado') ? 'selected' : ''; ?>>Pagado</option>
                <option value="Adeudado" <?php echo ($filter_estado_saldo == 'Adeudado') ? 'selected' : ''; ?>>Adeudado</option>
            </select>

            <label for="filter_agencia">Filtrar por agencia:</label>
            <select id="filter_agencia" name="filter_agencia">
                <option value="Todas las agencias">Todas las agencias</option>
                <?php
                // Obtener todas las agencias para el filtro
                $stmt_agencias = $pdo->query("SELECT DISTINCT nombre_agencia FROM pedidos_hyt");
                $agencias = $stmt_agencias->fetchAll(PDO::FETCH_ASSOC);
                foreach ($agencias as $agencia) {
                    echo '<option value="' . $agencia['nombre_agencia'] . '"' . (($filter_agencia == $agencia['nombre_agencia']) ? ' selected' : '') . '>' . $agencia['nombre_agencia'] . '</option>';
                }
                ?>
            </select>

            <label for="filter_destino">Filtrar por destino:</label>
            <select id="filter_destino" name="filter_destino">
                <option value="Todos los destinos">Todos los destinos</option>
                <?php
                // Obtener todos los destinos para el filtro
                $stmt_destinos = $pdo->query("SELECT DISTINCT nombre FROM destinos_hyt");
                $destinos = $stmt_destinos->fetchAll(PDO::FETCH_ASSOC);
                foreach ($destinos as $destino) {
                    echo '<option value="' . $destino['nombre'] . '"' . (($filter_destino == $destino['nombre']) ? ' selected' : '') . '>' . $destino['nombre'] . '</option>';
                }
                ?>
            </select>

            <label for="filter_fecha_entrega">Filtrar por fecha de entrega:</label>
            <input type="date" id="filter_fecha_entrega" name="filter_fecha_entrega" value="<?php echo $filter_fecha_entrega; ?>">

            <button type="submit">Filtrar</button>
        </form>

        <form method="GET" action="dashboard_hyt_admin.php">
            <button type="submit">Eliminar filtros</button>
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
            <tr class="<?php echo ($pedido['estado_saldo'] == 'Adeudado') ? 'adeudado-row' : 'pagado-row'; ?>">
                <td><?php echo htmlspecialchars($pedido['id']); ?></td>
                <td><?php echo htmlspecialchars($pedido['nombre_agencia']); ?></td>
                <td><?php echo formatearFecha($pedido['fecha_pedido']); ?></td>
                <td><?php echo formatearFecha($pedido['fecha_modificacion']); ?></td>
                <td><?php echo htmlspecialchars($pedido['interno']); ?></td>
                <td><?php echo htmlspecialchars($pedido['destino_nombre']); ?></td>
                <td><?php echo formatearFecha($pedido['fecha_salida']); ?></td>
                <td><?php echo htmlspecialchars($pedido['productos']); ?></td>
                <td><?php echo htmlspecialchars($pedido['precios']); ?> ARS</td>
                <td><?php echo number_format($pedido['total'], 2, ',', '.'); ?> ARS</td>
                <td><?php echo htmlspecialchars($pedido['estado_saldo']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
