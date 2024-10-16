<?php
session_start();
include '../includes/header_cuyo_placa.php';
include '../includes/db.php';
include '../includes/load_env.php';
loadEnv(__DIR__ . '/../.env');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para enviar correo electrónico
function enviarCorreo($to, $subject, $message) {
    // Configuraciones avanzadas con sendmail_path
    $from = getenv('SMTP_USERNAME');
    $headers = "From: $from\r\n" .
               "Reply-To: $from\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Ajustes sendmail_path para conexión básica, se define el comando directo si se permite sin autenticación SMTP completa
    ini_set('sendmail_path', "/usr/sbin/sendmail -t -i");

    // Enviar correo
    if (mail($to, $subject, $message, $headers)) {
        error_log("Correo enviado correctamente a: $to");
        return true;
    } else {
        error_log("Error al enviar correo a: $to");
        return false;
    }
}

// Verificar autenticación de usuario
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cuyo_placa') {
    header("Location: ../index.php");
    exit();
}

// Obtener el correo del cliente cuando carga la página
$correoCliente = "";
try {
    $stmt = $pdo->prepare("SELECT Correo FROM Usuarios WHERE Id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    $correoCliente = $usuario['Correo'] ?? '';
} catch (Exception $e) {
    error_log("Error al obtener el correo del cliente: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST['fecha'];
    $pedidos = $_POST['pedidos'];
    $detallePedido = "";

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO Pedidos_Cuyo_Placa (usuario_id, fecha, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$_SESSION['usuario_id'], $fecha]);
        $pedido_id = $pdo->lastInsertId();

        foreach ($pedidos as $turno => $plantas) {
            foreach ($plantas as $planta => $menus) {
                foreach ($menus as $menu => $cantidad) {
                    $stmt = $pdo->prepare("INSERT INTO Detalle_Pedidos_Cuyo_Placa (pedido_id, planta, turno, menu, cantidad) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$pedido_id, $planta, $turno, $menu, $cantidad]);
                    $detallePedido .= "Planta: $planta, Turno: $turno, Menú: $menu, Cantidad: $cantidad\n";
                }
            }
        }

        $pdo->commit();
        $success = true;
        
        $asunto = "Detalle del Pedido - Viandas Cuyo Placa";
        $mensaje = "Gracias por tu pedido. Aquí tienes el detalle:\n\n" . $detallePedido;
        
        if (!enviarCorreo($correoCliente, $asunto, $mensaje)) {
            $error = "No se pudo enviar el correo al cliente.";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Hubo un problema al guardar el pedido: " . $e->getMessage();
    }
}

// Estructura HTML y JavaScript para el modal y formulario, sin cambios en esta parte...
?>


$plantas = ['Aglomerado', 'Revestimiento', 'Impregnacion', 'Muebles', 'Transporte (Revestimiento)'];
$turnos_menus = [
    'Mañana' => ['Desayuno día siguiente', 'Almuerzo Caliente', 'Refrigerio sandwich almuerzo'],
    'Tarde' => ['Media tarde', 'Cena caliente', 'Refrigerio sandwich cena'],
    'Noche' => ['Desayuno noche', 'Sandwich noche']
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Viandas Cuyo Placa</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
            color: #343a40;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            border: 1px solid #e9ecef;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        input[type="date"] {
            padding: 8px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            font-size: 1em;
            width: 100%;
            box-sizing: border-box;
        }

        input[type="number"] {
            width: 60px;
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            text-align: center;
            font-size: 1em;
        }

        button {
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            display: block;
            margin: 20px auto;
        }

        button:hover {
            background-color: #0056b3;
        }

        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.2em;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.2em;
        }

        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
            border-radius: 10px;
        }

        .modal-content h2 {
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .modal-buttons {
            display: flex;
            justify-content: space-around;
        }

        .modal-buttons button {
            padding: 10px 20px;
            font-size: 1em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal-buttons .yes-button {
            background-color: #28a745;
            color: white;
        }

        .modal-buttons .no-button {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pedidos de Viandas - Cuyo Placa</h1>
        <?php if ($correoCliente): ?>
            <p>Correo de destino: <?php echo htmlspecialchars($correoCliente); ?></p>
        <?php endif; ?>
        <?php if (isset($success) && $success): ?>
            <p class="success-message">Pedidos guardados con éxito.</p>
        <?php elseif (isset($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form id="pedidoForm" method="post" action="pedidos_viandas_cuyo.php">
            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" required>
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">Planta</th>
                        <th colspan="3">Mañana</th>
                        <th colspan="3">Tarde</th>
                        <th colspan="2">Noche</th>
                    </tr>
                    <tr>
                        <th>Desayuno día siguiente</th>
                        <th>Almuerzo Caliente</th>
                        <th>Refrigerio sandwich almuerzo</th>
                        <th>Media tarde</th>
                        <th>Cena caliente</th>
                        <th>Refrigerio sandwich cena</th>
                        <th>Desayuno noche</th>
                        <th>Sandwich noche</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plantas as $planta) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($planta); ?></td>
                            <!-- Mañana -->
                            <td><input type="number" name="pedidos[Mañana][<?php echo $planta; ?>][Desayuno día siguiente]" min="0" value="0"></td>
                            <td><input type="number" name="pedidos[Mañana][<?php echo $planta; ?>][Almuerzo Caliente]" min="0" value="0"></td>
                            <td><input type="number" name="pedidos[Mañana][<?php echo $planta; ?>][Refrigerio sandwich almuerzo]" min="0" value="0"></td>
                            <!-- Tarde -->
                            <td><input type="number" name="pedidos[Tarde][<?php echo $planta; ?>][Media tarde]" min="0" value="0"></td>
                            <td><input type="number" name="pedidos[Tarde][<?php echo $planta; ?>][Cena caliente]" min="0" value="0"></td>
                            <td><input type="number" name="pedidos[Tarde][<?php echo $planta; ?>][Refrigerio sandwich cena]" min="0" value="0"></td>
                            <!-- Noche -->
                            <td><input type="number" name="pedidos[Noche][<?php echo $planta; ?>][Desayuno noche]" min="0" value="0"></td>
                            <td><input type="number" name="pedidos[Noche][<?php echo $planta; ?>][Sandwich noche]" min="0" value="0"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" onclick="showModal()">Guardar Pedidos</button>
        </form>
    </div>

    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <h2>¿Estás seguro de realizar este pedido?</h2>
            <div class="modal-buttons">
                <button class="yes-button" onclick="submitForm()">Sí</button>
                <button class="no-button" onclick="closeModal()">No</button>
            </div>
        </div>
    </div>

    <script>
        function showModal() { document.getElementById('confirmationModal').style.display = 'block'; }
        function closeModal() { document.getElementById('confirmationModal').style.display = 'none'; }
        function submitForm() { document.getElementById('pedidoForm').submit(); }
    </script>
</body>
</html>
