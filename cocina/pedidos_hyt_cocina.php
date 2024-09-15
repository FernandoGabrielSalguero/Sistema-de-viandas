<?php
session_start();
include '../includes/header_cocina.php';
include '../includes/db.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cocina') {
    header("Location: ../index.php");
    exit();
}

// Consulta para obtener los pedidos
$query = "SELECT p.id, p.fecha_pedido, p.fecha_salida, p.interno, p.observaciones, d.nombre AS destino_nombre, 
                 GROUP_CONCAT(CONCAT(dp.nombre, ' (', dp.cantidad, ')') SEPARATOR ', ') AS detalles, 
                 GROUP_CONCAT(CONCAT(dp.hora, ' ', dp.nombre) SEPARATOR ', ') AS descripciones 
          FROM pedidos_hyt p
          LEFT JOIN detalle_pedidos_hyt dp ON p.id = dp.pedido_id
          LEFT JOIN destinos_hyt d ON p.destino_id = d.id
          GROUP BY p.id 
          ORDER BY p.fecha_salida ASC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Cocina</title>
    <link rel="stylesheet" href="../css/style_pedidos_hyt_cocina.css">
</head>
<body>
    <h1>Pedidos Pendientes para Cocina</h1>

    <div class="filter-container">
        <form method="GET" action="pedidos_hyt_cocina.php">
            <label for="filter_fecha_salida">Filtrar por fecha de salida:</label>
            <input type="date" id="filter_fecha_salida" name="filter_fecha_salida">
            <label for="filter_interno">Filtrar por interno:</label>
            <select id="filter_interno" name="filter_interno">
                <option value="">Todos los internos</option>
                <?php
                // Consultar todos los internos
                $stmt_internos = $pdo->query("SELECT DISTINCT interno FROM pedidos_hyt ORDER BY interno");
                while ($interno = $stmt_internos->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $interno['interno'] . '">' . $interno['interno'] . '</option>';
                }
                ?>
            </select>
            <label for="filter_destino">Filtrar por destino:</label>
            <select id="filter_destino" name="filter_destino">
                <option value="">Todos los destinos</option>
                <?php
                // Consultar todos los destinos
                $stmt_destinos = $pdo->query("SELECT DISTINCT nombre FROM destinos_hyt ORDER BY nombre");
                while ($destino = $stmt_destinos->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $destino['nombre'] . '">' . $destino['nombre'] . '</option>';
                }
                ?>
            </select>
            <button type="submit">Filtrar</button>
        </form>
    </div>

    <div class="container">
        <?php foreach ($pedidos as $pedido): ?>
            <div class="pedido-card">
                <h3><?php echo htmlspecialchars($pedido['destino_nombre']); ?></h3>
                <p><strong>N° de Pedido: </strong><?php echo htmlspecialchars($pedido['id']); ?></p>
                <p><strong>Fecha de Pedido: </strong><?php echo date('d-m-Y', strtotime($pedido['fecha_pedido'])); ?></p>
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
                        $detalles = explode(', ', $pedido['descripciones']);
                        foreach ($detalles as $detalle):
                            list($hora, $descripcion) = explode(' ', $detalle, 2);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($hora); ?></td>
                            <td><?php echo htmlspecialchars($descripcion); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p><strong>Observaciones:</strong> <?php echo htmlspecialchars($pedido['observaciones']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
