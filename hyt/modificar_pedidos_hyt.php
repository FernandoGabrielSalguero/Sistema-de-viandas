<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_agencia') {
    header("Location: ../login.php");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';
include '../includes/header_hyt_agencia.php';

// Establecer la zona horaria de Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Obtener la fecha y hora actual
$currentDate = date('Y-m-d');
$currentTime = date('H:i');

// Procesar la actualización del pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modificar_pedido'])) {
    $pedido_id = $_POST['pedido_id'];
    $interno = $_POST['interno'];
    $hora_salida = $_POST['hora_salida'];
    $destino_id = isset($_POST['destino']) ? $_POST['destino'] : null; // Asegurarse de que el valor exista
    $observaciones = $_POST['observaciones'];
    $fecha_salida = $_POST['fecha_salida'];

    if ($destino_id) { // Verificar que el destino haya sido enviado
        // Actualizar el pedido en la tabla pedidos_hyt
        $stmt = $pdo->prepare("UPDATE pedidos_hyt SET interno = ?, hora_salida = ?, destino_id = ?, observaciones = ?, fecha_salida = ? WHERE id = ?");
        if ($stmt->execute([$interno, $hora_salida, $destino_id, $observaciones, $fecha_salida, $pedido_id])) {
            // Actualizar el detalle del pedido
            foreach ($_POST['productos'] as $producto_id => $cantidad) {
                $stmt_detalle = $pdo->prepare("UPDATE detalle_pedidos_hyt SET cantidad = ? WHERE pedido_id = ? AND id = ?");
                $stmt_detalle->execute([$cantidad, $pedido_id, $producto_id]);
            }
            echo "Pedido actualizado con éxito";
        } else {
            $error = $stmt->errorInfo();
            echo "Error al actualizar el pedido: " . $error[2];
        }
    } else {
        echo "Error: No se ha seleccionado un destino.";
    }
}

// Obtener los pedidos del usuario hyt_agencia actual
$usuario_id = $_SESSION['usuario_id'];
$query = "SELECT p.id, p.fecha_pedido, p.fecha_salida, p.hora_salida, p.interno, p.estado, p.observaciones, d.nombre as destino_nombre, p.destino_id 
          FROM pedidos_hyt p
          LEFT JOIN destinos_hyt d ON p.destino_id = d.id
          WHERE p.nombre_agencia = (SELECT Nombre FROM Usuarios WHERE Id = ?) ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los destinos para el selector
$stmt_destinos = $pdo->query("SELECT id, nombre FROM destinos_hyt");
$destinos = $stmt_destinos->fetchAll(PDO::FETCH_ASSOC);
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
        .table-container {
            width: 100%;
            margin: 0 auto;
            max-width: 1000px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        table th {
            background-color: #f2f2f2;
        }
        .form-control {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .button {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        .button[disabled] {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .button-container {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Modificar Pedidos HYT</h1>

    <div class="table-container">
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
                        <form method="POST" action="">
                            <td><?php echo $pedido['id']; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($pedido['fecha_pedido'])); ?></td>
                            <td><input type="date" name="fecha_salida" class="form-control" value="<?php echo $pedido['fecha_salida']; ?>"></td>
                            <td><input type="time" name="hora_salida" class="form-control" value="<?php echo $pedido['hora_salida']; ?>"></td>
                            <td><input type="text" name="interno" class="form-control" value="<?php echo $pedido['interno']; ?>"></td>
                            <td>
                                <select name="destino" class="form-control">
                                    <?php foreach ($destinos as $destino): ?>
                                        <option value="<?php echo $destino['id']; ?>" <?php echo ($destino['id'] == $pedido['destino_id']) ? 'selected' : ''; ?>>
                                            <?php echo $destino['nombre']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><?php echo $pedido['estado']; ?></td>
                            <td><input type="text" name="observaciones" class="form-control" value="<?php echo $pedido['observaciones']; ?>"></td>
                            <td>
                                <?php
                                $detalleQuery = "SELECT id, nombre, cantidad FROM detalle_pedidos_hyt WHERE pedido_id = ?";
                                $detalleStmt = $pdo->prepare($detalleQuery);
                                $detalleStmt->execute([$pedido['id']]);
                                $detalles = $detalleStmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($detalles as $detalle):
                                ?>
                                    <label><?php echo $detalle['nombre']; ?>:</label>
                                    <input type="number" name="productos[<?php echo $detalle['id']; ?>]" class="form-control" value="<?php echo $detalle['cantidad']; ?>"><br>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php
                                $currentTime = date('H:i');
                                $disableButton = ($currentDate === $pedido['fecha_salida'] && $currentTime >= '11:00') ? 'disabled' : '';
                                ?>
                                <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                <button type="submit" name="modificar_pedido" class="button" <?php echo $disableButton; ?>>Modificar</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
