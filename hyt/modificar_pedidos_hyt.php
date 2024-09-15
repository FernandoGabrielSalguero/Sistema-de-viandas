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

// Obtener todos los pedidos del usuario hyt_agencia actual
$query = "SELECT p.id, p.nombre_agencia, p.fecha_pedido, p.fecha_salida, p.hora_salida, p.estado, p.interno, p.destino_id, p.observaciones, d.nombre as destino_nombre
          FROM pedidos_hyt p
          LEFT JOIN destinos_hyt d ON p.destino_id = d.id
          WHERE p.nombre_agencia = ?
          ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$nombre_agencia]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los destinos para el menú desplegable
$stmt_destinos = $pdo->prepare("SELECT id, nombre FROM destinos_hyt");
$stmt_destinos->execute();
$destinos = $stmt_destinos->fetchAll(PDO::FETCH_ASSOC);

// Lógica para actualizar el pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modificar_pedido'])) {
    $pedido_id = $_POST['pedido_id'];
    $interno = $_POST['interno'];
    $destino_id = $_POST['destino'];
    $fecha_salida = $_POST['fecha_salida'];
    $hora_salida = $_POST['hora_salida'];
    $observaciones = $_POST['observaciones'];

    // Actualizar la tabla pedidos_hyt
    $stmt_update = $pdo->prepare("UPDATE pedidos_hyt SET interno = ?, destino_id = ?, fecha_salida = ?, hora_salida = ?, observaciones = ? WHERE id = ?");
    $stmt_update->execute([$interno, $destino_id, $fecha_salida, $hora_salida, $observaciones, $pedido_id]);

    // Actualizar las cantidades de los menús
    foreach ($_POST['productos'] as $producto_id => $cantidad) {
        $stmt_update_detalle = $pdo->prepare("UPDATE detalle_pedidos_hyt SET cantidad = ? WHERE pedido_id = ? AND nombre = ?");
        $stmt_update_detalle->execute([$cantidad, $pedido_id, $producto_id]);
    }

    // Enviar el correo después de modificar el pedido
    $correo_agencia = $_SESSION['correo']; // Asumiendo que el correo está en la sesión
    $subject = "Pedido Modificado";
    $message = "Se ha modificado el pedido con ID $pedido_id. Los nuevos detalles son:\n\n";
    $message .= "Interno: $interno\nDestino: $destino_id\nFecha de Salida: $fecha_salida\nHora de Salida: $hora_salida\nObservaciones: $observaciones";

    mail($correo_agencia, $subject, $message);

    echo "Pedido modificado correctamente y correo enviado.";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Pedidos HYT</title>
    <link rel="stylesheet" href="../css/hyt_variables.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            text-align: center;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
        }

        th {
            background-color: #f4f4f4;
        }

        .button {
            background-color: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button[disabled] {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Modificar Pedidos HYT</h1>
        <table>
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Fecha Pedido</th>
                    <th>Fecha Salida</th>
                    <th>Hora Salida</th>
                    <th>Interno</th>
                    <th>Destino</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th>Menús (Cantidad)</th>
                    <th>Modificar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <form method="POST" action="modificar_pedidos_hyt.php">
                            <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars($pedido['id']); ?>">
                            <td><?php echo htmlspecialchars($pedido['id']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($pedido['fecha_pedido'])); ?></td>
                            <td><input type="date" name="fecha_salida" value="<?php echo $pedido['fecha_salida']; ?>"></td>
                            <td><input type="time" name="hora_salida" value="<?php echo $pedido['hora_salida']; ?>"></td>
                            <td><input type="text" name="interno" value="<?php echo htmlspecialchars($pedido['interno']); ?>"></td>
                            <td>
                                <select name="destino">
                                    <?php foreach ($destinos as $destino): ?>
                                        <option value="<?php echo $destino['id']; ?>" <?php echo ($destino['id'] == $pedido['destino_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($destino['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><?php echo htmlspecialchars($pedido['estado']); ?></td>
                            <td><textarea name="observaciones"><?php echo htmlspecialchars($pedido['observaciones']); ?></textarea></td>

                            <!-- Obtener la cantidad de menús -->
                            <td>
                                <?php
                                $detalleQuery = "SELECT nombre, cantidad FROM detalle_pedidos_hyt WHERE pedido_id = ?";
                                $detalleStmt = $pdo->prepare($detalleQuery);
                                $detalleStmt->execute([$pedido['id']]);
                                $detalles = $detalleStmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($detalles as $detalle): ?>
                                    <label><?php echo htmlspecialchars($detalle['nombre']); ?>:</label>
                                    <input type="number" name="productos[<?php echo $detalle['nombre']; ?>]" value="<?php echo $detalle['cantidad']; ?>"><br>
                                <?php endforeach; ?>
                            </td>

                            <!-- Mostrar el botón solo si la hora y fecha son válidas -->
                            <td>
                                <?php if ($pedido['fecha_salida'] === $currentDate && $currentTime < '11:00'): ?>
                                    <button type="submit" class="button" name="modificar_pedido">Modificar</button>
                                <?php else: ?>
                                    <button class="button" disabled>Modificar</button>
                                <?php endif; ?>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
