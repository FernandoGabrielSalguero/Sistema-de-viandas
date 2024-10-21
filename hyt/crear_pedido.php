<?php
session_start();
include '../includes/header_cuyo_placa.php';
include '../includes/db.php';
include '../includes/load_env.php';

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cuyo_placa') {
    header("Location: ../index.php");
    exit();
}

$resumen_pedido = [];
$fecha_pedido = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST['fecha'];
    $fecha_pedido = $fecha;
    $pedidos = $_POST['pedidos'];

    // Iniciar transacción
    $pdo->beginTransaction();

    try {
        // Insertar el nuevo pedido en la tabla Pedidos_Cuyo_Placa
        $stmt = $pdo->prepare("INSERT INTO Pedidos_Cuyo_Placa (usuario_id, fecha, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$_SESSION['usuario_id'], $fecha]);

        // Obtener el ID del pedido recién insertado
        $pedido_id = $pdo->lastInsertId();

        foreach ($pedidos as $turno => $plantas) {
            foreach ($plantas as $planta => $menus) {
                foreach ($menus as $menu => $cantidad) {
                    if ($cantidad > 0) {  // Solo guardar cantidades mayores a 0
                        // Insertar cada detalle del pedido en la tabla Detalle_Pedidos_Cuyo_Placa
                        $stmt = $pdo->prepare("INSERT INTO Detalle_Pedidos_Cuyo_Placa (pedido_id, planta, turno, menu, cantidad)
                                               VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$pedido_id, $planta, $turno, $menu, $cantidad]);

                        // Agregar detalle al resumen
                        $resumen_pedido[] = [
                            'planta' => $planta,
                            'turno' => $turno,
                            'menu' => $menu,
                            'cantidad' => $cantidad
                        ];
                    }
                }
            }
        }

        // Confirmar la transacción
        $pdo->commit();
        $success = true; // Indicar que el pedido se guardó con éxito

        // Preparar el mensaje del correo
        $subject = "Resumen de Pedido de Viandas - ID Pedido: " . $pedido_id;
        $message = "Fecha del Pedido: $fecha_pedido\n\n";
        $message .= "Resumen de lo solicitado:\n";
        foreach ($resumen_pedido as $detalle) {
            $message .= "Planta: {$detalle['planta']}, Turno: {$detalle['turno']}, Menú: {$detalle['menu']}, Cantidad: {$detalle['cantidad']}\n";
        }

        // Enviar correo a los destinatarios
        $to = "fernandosalguero685@gmail.com, florenciaivonnediaz@gmail.com, asd@gmail.com, federicofigeroa400@gmail.com";
        if (!enviarCorreo($to, $subject, $message)) {
            echo "Error al enviar el correo.";
        } else {
            echo "Pedido realizado correctamente y correo enviado.";
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
    <title>Realizar Pedido</title>
    <link rel="stylesheet" href="../css/style_hyt_agencia.css">
</head>
<body>

    <h1>Realizar un nuevo pedido</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>

    <form method="POST" action="">
        <label for="nombre_agencia">Nombre de la Agencia:</label>
        <input type="text" id="nombre_agencia" name="nombre_agencia" value="<?php echo $nombre_agencia; ?>" disabled>

        <label for="correo_agencia">Correo electrónico de la Agencia:</label>
        <input type="text" id="correo_agencia" name="correo_agencia" value="<?php echo $correo_agencia; ?>" disabled>

        <label for="destino">Seleccionar destino:</label>
        <select id="destino" name="destino" required>
            <?php foreach ($destinos as $destino): ?>
                <option value="<?php echo $destino['id']; ?>"><?php echo $destino['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="interno">Interno (Número de interno):</label>
        <input type="number" id="interno" name="interno" required>

        <label for="fecha_salida">Fecha de salida:</label>
        <input type="date" id="fecha_salida" name="fecha_salida" required>

        <label for="hora_salida">Hora de salida:</label>
        <input type="time" id="hora_salida" name="hora_salida" required>

        <label for="observaciones">Observaciones:</label>
        <textarea id="observaciones" name="observaciones" placeholder="Escriba cualquier observación sobre el pedido"></textarea>

        <h2>Detalle del pedido</h2>

        <?php foreach ($productos as $producto): ?>
            <label for="producto_<?php echo $producto['id']; ?>"><?php echo $producto['nombre']; ?> (Precio: <?php echo $producto['precio']; ?>):</label>
            <input type="number" id="producto_<?php echo $producto['id']; ?>" name="productos[<?php echo $producto['id']; ?>]" min="0" value="0">
        <?php endforeach; ?>

        <button type="submit" name="realizar_pedido">Realizar Pedido</button>
    </form>

</body>
</html>

