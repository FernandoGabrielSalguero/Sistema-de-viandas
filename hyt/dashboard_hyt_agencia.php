<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_agencia') {
    header("Location: ../login.php");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_hyt_agencia.php';
include '../includes/db.php';

// Establecer la zona horaria de Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Obtener la fecha y hora actual
$currentDate = date('Y-m-d');
$currentTime = date('H:i');

// Obtener los datos del usuario actual desde la tabla Usuarios
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT Nombre FROM Usuarios WHERE Id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombre_agencia = $usuario['Nombre'];

// Obtener los pedidos del usuario hyt_agencia actual
$query = "SELECT p.id, p.nombre_agencia, p.fecha_pedido, p.fecha_modificacion, p.fecha_salida, p.estado, p.interno, p.hora_salida, p.destino_id, p.observaciones, p.estado_saldo, d.nombre as destino_nombre
          FROM pedidos_hyt p
          LEFT JOIN destinos_hyt d ON p.destino_id = d.id
          WHERE p.nombre_agencia = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$nombre_agencia]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filter_fecha_salida = isset($_GET['filter_fecha_salida']) ? $_GET['filter_fecha_salida'] : null;

if ($filter_fecha_salida) {
    $query .= " AND p.fecha_salida = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$nombre_agencia, $filter_fecha_salida]);
} else {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$nombre_agencia]);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard HYT Agencia</title>
    <link rel="stylesheet" href="../css/hyt_variables.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .pedido-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            max-width: 450px;
            flex: 1 1 calc(33.333% - 40px);
            box-sizing: border-box;
            margin-bottom: 20px;
            text-align: center;
        }

        .pedido-card h3 {
            margin: 0;
            font-size: 1.5em;
            color: #007bff;
        }

        .pedido-card table {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }

        .pedido-card table, .pedido-card th, .pedido-card td {
            border: 1px solid #ddd;
        }

        .pedido-card th, .pedido-card td {
            padding: 8px;
            text-align: center;
        }

        .pedido-card .estado {
            margin-top: 10px;
        }

        .pedido-card .button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin: 0 auto;
        }

        .pedido-card .button[disabled] {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .estado-saldo {
            font-weight: bold;
        }

        .estado-saldo.adeudado {
            color: red;
        }

        .estado-saldo.pagado {
            color: green;
        }

        @media (max-width: 768px) {
            .pedido-card {
                flex: 1 1 calc(50% - 40px);
            }
        }

        @media (max-width: 480px) {
            .pedido-card {
                flex: 1 1 100%;
            }
        }

        /* Estilos del filtro */
        .filter-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .filter-container label {
            font-weight: bold;
            margin-right: 10px;
        }

        .filter-container input[type="date"] {
            padding: 5px;
            font-size: 1em;
        }

        .filter-container button {
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

    </style>
</head>
<body>
    <h1 style="text-align: center;">Tus Pedidos de Viandas</h1>

    <!-- Filtro de fecha de salida -->
    <div class="filter-container">
        <form method="GET" action="dashboard_hyt_agencia.php">
            <label for="filter_fecha_salida">Filtrar por fecha de salida:</label>
            <input type="date" id="filter_fecha_salida" name="filter_fecha_salida" value="<?php echo isset($_GET['filter_fecha_salida']) ? $_GET['filter_fecha_salida'] : ''; ?>">
            <button type="submit">Filtrar</button>
        </form>
    </div>

    <div class="container">
        <?php if (count($pedidos) > 0): ?>
            <?php foreach ($pedidos as $pedido): ?>
                <div class="pedido-card">
                    <h3><?php echo htmlspecialchars($pedido['destino_nombre']); ?></h3>
                    <p><strong>N° de Pedido: </strong><?php echo htmlspecialchars($pedido['id']); ?></p>
                    <p><strong>Fecha de Pedido: </strong><?php echo date('d-m-Y', strtotime($pedido['fecha_pedido'])); ?></p>
                    <p><strong>Fecha de Salida: </strong><?php echo date('d-m-Y', strtotime($pedido['fecha_salida'])); ?></p>
                    <p><strong>Interno: </strong><?php echo htmlspecialchars($pedido['interno']); ?></p>
                    <p><strong>Estado de Saldo: </strong>
                        <span class="estado-saldo <?php echo ($pedido['estado_saldo'] == 'Adeudado') ? 'adeudado' : 'pagado'; ?>">
                            <?php echo htmlspecialchars($pedido['estado_saldo']); ?>
                        </span>
                    </p>

                    <table>
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $detalleQuery = "SELECT nombre, cantidad, precio FROM detalle_pedidos_hyt WHERE pedido_id = ?";
                            $detalleStmt = $pdo->prepare($detalleQuery);
                            $detalleStmt->execute([$pedido['id']]);
                            $detalles = $detalleStmt->fetchAll(PDO::FETCH_ASSOC);

                            $total = 0;

                            foreach ($detalles as $detalle):
                                $subtotal = $detalle['cantidad'] * $detalle['precio'];
                                $total += $subtotal;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pedido['hora_salida']); ?></td>
                                    <td><?php echo htmlspecialchars($detalle['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($detalle['precio'], 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <p><strong>Total: <?php echo number_format($total, 2); ?> ARS</strong></p>

                    <p><strong>Observaciones:</strong> <?php echo htmlspecialchars($pedido['observaciones']); ?></p>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">No hay pedidos disponibles.</p>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="crear_pedido.php" class="button">Crear nuevo pedido</a>
    </div>
</body>
</html>
