<?php
session_start();
include '../includes/header_cuyo_placa.php';
include '../includes/db.php';
include '../includes/load_env.php';

// Cargar variables del archivo .env
loadEnv(__DIR__ . '/../.env');

// Función para enviar correo electrónico usando SMTP
function enviarCorreo($to, $subject, $message) {
    $headers = "From: " . getenv('SMTP_USERNAME') . "\r\n" .
               "Reply-To: " . getenv('SMTP_USERNAME') . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    ini_set('SMTP', getenv('SMTP_HOST'));
    ini_set('smtp_port', getenv('SMTP_PORT'));
    ini_set('sendmail_from', getenv('SMTP_USERNAME'));

    return mail($to, $subject, $message, $headers);
}

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cuyo_placa') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviarCorreo']) && $_POST['enviarCorreo'] == "1") {
    $fecha = $_POST['fecha'];
    $pedidos = $_POST['pedidos'];

    // Iniciar transacción
    $pdo->beginTransaction();

    try {
        // Insertar el nuevo pedido en la tabla Pedidos_Cuyo_Placa
        $stmt = $pdo->prepare("INSERT INTO Pedidos_Cuyo_Placa (usuario_id, fecha, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$_SESSION['usuario_id'], $fecha]);

        // Obtener el ID del pedido recién insertado
        $pedido_id = $pdo->lastInsertId();

        // Recopilar detalles del pedido para el correo electrónico
        $detallesPedido = "";
        foreach ($pedidos as $turno => $plantas) {
            foreach ($plantas as $planta => $menus) {
                foreach ($menus as $menu => $cantidad) {
                    // Insertar cada detalle del pedido en la tabla Detalle_Pedidos_Cuyo_Placa
                    $stmt = $pdo->prepare("INSERT INTO Detalle_Pedidos_Cuyo_Placa (pedido_id, planta, turno, menu, cantidad)
                                           VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$pedido_id, $planta, $turno, $menu, $cantidad]);

                    // Añadir detalles al mensaje del correo
                    $detallesPedido .= "$turno - $planta - $menu: $cantidad\n";
                }
            }
        }

        // Confirmar la transacción
        $pdo->commit();
        $success = true; // Indicar que el pedido se guardó con éxito

        // Enviar el correo electrónico de confirmación
        $to = $_SESSION['correo'];
        $subject = "Confirmación de Pedido";
        $message = "Su pedido ha sido confirmado:\n\n" . $detallesPedido;
        
        if (!enviarCorreo($to, $subject, $message)) {
            $error = "Hubo un problema al enviar el correo de confirmación.";
        }
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        $error = "Hubo un problema al guardar el pedido: " . $e->getMessage();
    }
}

// Definir las plantas, turnos y menús
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
        <h4>Enviaremos comprobante a la siguiente dirección: <?php echo $_SESSION['correo']; ?></h4>

        <?php if (isset($success) && $success) : ?>
            <p class="success-message">Pedidos guardados con éxito. Se ha enviado un correo de confirmación.</p>
        <?php elseif (isset($error)) : ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form id="pedidoForm" method="post" action="pedidos_viandas_cuyo.php">
            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" required>

            <table>
                <!-- Estructura de tabla de pedidos (plantas, turnos, etc.) -->
            </table>

            <!-- Campo oculto para indicar el envío del correo -->
            <input type="hidden" id="enviarCorreo" name="enviarCorreo" value="0">

            <!-- Botón para guardar el pedido -->
            <button type="button" onclick="showModal()">Guardar Pedidos</button>
        </form>
    </div>

    <!-- Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <h2>¿Estas seguro de realizar este pedido?</h2>
            <div class="modal-buttons">
                <button class="yes-button" onclick="submitForm()">SI</button>
                <button class="no-button" onclick="closeModal()">NO</button>
            </div>
        </div>
    </div>

    <script>
        // Mostrar el modal
        function showModal() {
            document.getElementById('confirmationModal').style.display = 'block';
        }

        // Cerrar el modal
        function closeModal() {
            document.getElementById('confirmationModal').style.display = 'none';
        }

        // Enviar el formulario
        function submitForm() {
            document.getElementById('enviarCorreo').value = "1";
            document.getElementById('pedidoForm').submit();
        }
    </script>
</body>
</html>
