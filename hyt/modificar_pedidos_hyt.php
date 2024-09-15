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
$query = "SELECT p.id, p.nombre_agencia, p.fecha_pedido, p.fecha_salida, p.estado, p.interno, p.destino_id, p.observaciones, d.nombre as destino_nombre
          FROM pedidos_hyt p
          LEFT JOIN destinos_hyt d ON p.destino_id = d.id
          WHERE p.nombre_agencia = ?
          ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$nombre_agencia]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                        <td><?php echo htmlspecialchars($pedido['id']); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($pedido['fecha_pedido'])); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($pedido['fecha_salida'])); ?></td>
                        <td><?php echo htmlspecialchars($pedido['interno']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['destino_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['estado']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['observaciones']); ?></td>

                        <!-- Obtener la cantidad de menús -->
                        <td>
                            <?php
                            $detalleQuery = "SELECT nombre, cantidad FROM detalle_pedidos_hyt WHERE pedido_id = ?";
                            $detalleStmt = $pdo->prepare($detalleQuery);
                            $detalleStmt->execute([$pedido['id']]);
                            $detalles = $detalleStmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($detalles as $detalle):
                                echo htmlspecialchars($detalle['nombre']) . ": " . htmlspecialchars($detalle['cantidad']) . "<br>";
                            endforeach;
                            ?>
                        </td>

                        <td>
                            <?php
                            // Mostrar botón "Modificar" solo si la fecha de salida es hoy y antes de las 11:00 AM
                            if ($pedido['fecha_salida'] === $currentDate && $currentTime < '11:00'): ?>
                                <form action="modificar_pedido_form.php" method="GET">
                                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                    <button type="submit" class="button">Modificar</button>
                                </form>
                            <?php else: ?>
                                <button class="button" disabled>Modificar</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
