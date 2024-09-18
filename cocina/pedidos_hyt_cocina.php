<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cocina') {
    header("Location: ../login.php");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_cocina.php';
include '../includes/db.php';

// Establecer la zona horaria de Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Obtener la fecha y hora actual
$currentDate = date('Y-m-d');
$currentTime = date('H:i');

// Obtener los destinos disponibles
$stmt_destinos = $pdo->prepare("SELECT id, nombre FROM destinos_hyt");
$stmt_destinos->execute();
$destinos = $stmt_destinos->fetchAll(PDO::FETCH_ASSOC);

// Obtener los internos de micros disponibles
$stmt_internos = $pdo->prepare("SELECT DISTINCT interno FROM pedidos_hyt");
$stmt_internos->execute();
$internos = $stmt_internos->fetchAll(PDO::FETCH_ASSOC);

// Obtener los pedidos del usuario cocina actual
$query = "SELECT p.id, p.nombre_agencia, p.fecha_pedido, p.fecha_modificacion, p.fecha_salida, p.estado, p.interno, p.hora_salida, p.destino_id, p.observaciones, d.nombre as destino_nombre
          FROM pedidos_hyt p
          LEFT JOIN destinos_hyt d ON p.destino_id = d.id
          WHERE p.estado = 'vigente'";  // Sólo mostrar pedidos vigentes

$filter_fecha_salida = isset($_GET['filter_fecha_salida']) ? $_GET['filter_fecha_salida'] : null;
$filter_interno = isset($_GET['filter_interno']) ? $_GET['filter_interno'] : null;
$filter_destino = isset($_GET['filter_destino']) ? $_GET['filter_destino'] : null;

// Añadimos condiciones a la consulta si existen filtros
if ($filter_fecha_salida) {
    $query .= " AND p.fecha_salida = ?";
}
if ($filter_interno) {
    $query .= " AND p.interno = ?";
}
if ($filter_destino) {
    $query .= " AND p.destino_id = ?";
}

// Preparamos y ejecutamos la consulta en función de los filtros
$stmt = $pdo->prepare($query);
$params = [];

if ($filter_fecha_salida) {
    $params[] = $filter_fecha_salida;
}
if ($filter_interno) {
    $params[] = $filter_interno;
}
if ($filter_destino) {
    $params[] = $filter_destino;
}

$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Cocina</title>
    <link rel="stylesheet" href="../css/style_pedidos_hyt_cocina.css">
</head>
<body>
    <h1 style="text-align: center;">Pedidos Pendientes para Cocina</h1>

    <!-- Filtro de fecha de salida, internos y destinos -->
    <div class="filter-container">
        <form method="GET" action="pedidos_hyt_cocina.php">
            <label for="filter_fecha_salida">Filtrar por fecha de salida:</label>
            <input type="date" id="filter_fecha_salida" name="filter_fecha_salida" value="<?php echo isset($_GET['filter_fecha_salida']) ? $_GET['filter_fecha_salida'] : ''; ?>">

            <label for="filter_interno">Filtrar por interno:</label>
            <select id="filter_interno" name="filter_interno">
                <option value="">Todos los internos</option>
                <?php foreach ($internos as $interno): ?>
                    <option value="<?php echo htmlspecialchars($interno['interno']); ?>" <?php echo (isset($_GET['filter_interno']) && $_GET['filter_interno'] == $interno['interno']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($interno['interno']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="filter_destino">Filtrar por destino:</label>
            <select id="filter_destino" name="filter_destino">
                <option value="">Todos los destinos</option>
                <?php foreach ($destinos as $destino): ?>
                    <option value="<?php echo htmlspecialchars($destino['id']); ?>" <?php echo (isset($_GET['filter_destino']) && $_GET['filter_destino'] == $destino['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($destino['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Filtrar</button>
        </form>
    </div>

    <div class="container">
        <?php if (count($pedidos) > 0): ?>
            <?php foreach ($pedidos as $pedido): ?>
                <div class="pedido-card">
                    <h3><?php echo htmlspecialchars($pedido['destino_nombre']); ?></h3>
                    <p><strong>N° de Pedido: </strong><?php echo htmlspecialchars($pedido['id']); ?></p>
                    <p><strong>Fecha de Salida: </strong><?php echo date('d-m-Y', strtotime($pedido['fecha_salida'])); ?></p>
                    <p><strong>Interno: </strong><?php echo htmlspecialchars($pedido['interno']); ?></p>

                    <table>
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $detalleQuery = "SELECT nombre, cantidad FROM detalle_pedidos_hyt WHERE pedido_id = ?";
                            $detalleStmt = $pdo->prepare($detalleQuery);
                            $detalleStmt->execute([$pedido['id']]);
                            $detalles = $detalleStmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($detalles as $detalle):
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pedido['hora_salida']); ?></td>
                                    <td><?php echo htmlspecialchars($detalle['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <p><strong>Observaciones:</strong> <?php echo htmlspecialchars($pedido['observaciones']); ?></p>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">No hay pedidos disponibles.</p>
        <?php endif; ?>
    </div>

</body>
</html>