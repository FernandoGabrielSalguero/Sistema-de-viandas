<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_agencia') {
    header("Location: ../login.php");
    exit();
}

// Habilitar errores
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

// Obtener los pedidos del usuario hyt_agencia actual
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT Nombre FROM Usuarios WHERE Id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombre_agencia = $usuario['Nombre'];

// Obtener todos los pedidos, ordenados por ID de mayor a menor
$query = "SELECT p.id, p.fecha_pedido, p.fecha_salida, p.estado, p.interno, p.hora_salida, p.destino_id, p.observaciones, p.estado_saldo, d.nombre as destino_nombre
          FROM pedidos_hyt p
          LEFT JOIN destinos_hyt d ON p.destino_id = d.id
          WHERE p.nombre_agencia = ?
          ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$nombre_agencia]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Enviar correo al modificar pedido
function enviarCorreo($to, $subject, $message) {
    $headers = "From: no-reply@yourdomain.com\r\n";
    return mail($to, $subject, $message, $headers);
}

// Si el formulario de modificación ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modificar_pedido'])) {
    $pedido_id = $_POST['pedido_id'];
    $observaciones = $_POST['observaciones'];

    // Actualizar el pedido
    $stmt_update = $pdo->prepare("UPDATE pedidos_hyt SET observaciones = ? WHERE id = ?");
    $stmt_update->execute([$observaciones, $pedido_id]);

    // Enviar el correo
    $correo = $usuario['Correo'];  // Correo del usuario
    $subject = "Pedido Modificado: Pedido #" . $pedido_id;
    $message = "Su pedido #" . $pedido_id . " ha sido modificado.\n\nObservaciones: " . $observaciones;
    enviarCorreo($correo, $subject, $message);

    // Redirigir después de modificar
    header("Location: modificar_pedidos_hyt.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Pedidos HYT</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin: 20px 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .button {
            background-color: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: inline-block;
        }

        .button[disabled] {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .button.red {
            background-color: red;
        }

        .button.modify {
            background-color: #007bff;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }

        .close {
            float: right;
            font-size: 1.5em;
            cursor: pointer;
        }

        .input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
    <script>
        function showModal(id, obs) {
            document.getElementById('pedido_id').value = id;
            document.getElementById('observaciones').value = obs;
            document.getElementById('modal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('modal')) {
                closeModal();
            }
        }
    </script>
</head>
<body>
    <h1>Modificar Pedidos HYT</h1>
    <div class="container">
        <div class="table-container">
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
                        <th>Modificar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <?php
                        // Verificar si el botón debe mostrarse o no
                        $show_button = ($currentDate === $pedido['fecha_salida'] && $currentTime < '11:00') ? true : false;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pedido['id']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($pedido['fecha_pedido'])); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($pedido['fecha_salida'])); ?></td>
                            <td><?php echo htmlspecialchars($pedido['interno']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['destino_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['estado']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['observaciones']); ?></td>
                            <td>
                                <?php if ($show_button): ?>
                                    <button class="button modify" onclick="showModal(<?php echo $pedido['id']; ?>, '<?php echo htmlspecialchars($pedido['observaciones']); ?>')">Modificar</button>
                                <?php else: ?>
                                    <button class="button" disabled>Modificar</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Modificar Pedido</h2>
            <form method="POST" action="">
                <input type="hidden" name="pedido_id" id="pedido_id">
                <label for="observaciones">Observaciones:</label>
                <textarea class="input" name="observaciones" id="observaciones" rows="4" required></textarea>
                <button type="submit" name="modificar_pedido" class="button">Guardar cambios</button>
            </form>
        </div>
    </div>
</body>
</html>
