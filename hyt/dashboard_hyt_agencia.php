<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_agencia') {
    header("Location: ../login.php");
    exit();
}
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_hyt_agencia.php';
include '../includes/db.php';

// Obtener la fecha y hora actual
$currentDate = date('Y-m-d');
$currentTime = date('H:i');

// Obtener los pedidos del usuario hyt_agencia actual
$agencia_id = $_SESSION['usuario_id'];

$query = "SELECT p.id, p.fecha_pedido, p.estado, p.interno, p.hora_salida, p.observaciones, p.destino_id, d.nombre as destino_nombre
          FROM pedidos_hyt p
          LEFT JOIN destinos_hyt d ON p.destino_id = d.id
          WHERE p.nombre_agencia = ?";

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
    <style>
        .pedido-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            background-color: #fff;
            max-width: 400px;
            margin: 20px auto;
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
        }

        .pedido-card .button[disabled] {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Tus Pedidos de Viandas</h1>

    <?php if (count($pedidos) > 0): ?>
        <?php foreach ($pedidos as $pedido): ?>
            <div class="pedido-card">
                <h3><?php echo htmlspecialchars($pedido['destino_nombre']); ?></h3>
                <p><strong>N° de Pedido: </strong><?php echo htmlspecialchars($pedido['id']); ?></p>
                <p><strong>Fecha de Pedido: </strong><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></p>
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

                        foreach ($detalles as $detalle): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pedido['hora_salida']); ?></td>
                                <td><?php echo htmlspecialchars($detalle['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p><strong>Observaciones:</strong> <?php echo htmlspecialchars($pedido['observaciones']); ?></p>

                <?php
                // Determinar si el botón de actualización debe estar habilitado o deshabilitado
                $isDisabled = ($currentDate === $pedido['fecha_pedido'] && $currentTime < '10:00') ? '' : 'disabled';
                ?>
                <button class="button" <?php echo $isDisabled; ?>>Actualizar</button>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center;">No hay pedidos disponibles.</p>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 20px;">
        <a href="crear_pedido.php" class="button">Crear nuevo pedido</a>
    </div>
</body>
</html>
