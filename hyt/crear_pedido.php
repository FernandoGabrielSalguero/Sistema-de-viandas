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

include '../includes/db.php';
include '../includes/header_hyt_agencia.php';
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

// Obtener el nombre y correo de la agencia (usuario actual)
$agencia_id = $_SESSION['usuario_id'];
$stmt_agencia = $pdo->prepare("SELECT Usuario, Correo FROM Usuarios WHERE Id = ?");
$stmt_agencia->execute([$agencia_id]);
$agencia_data = $stmt_agencia->fetch(PDO::FETCH_ASSOC);
$nombre_agencia = $agencia_data['Usuario'];  
$correo_agencia = $agencia_data['Correo'];  

// Obtener destinos y productos
$stmt_destinos = $pdo->prepare("SELECT id, nombre FROM destinos_hyt");
$stmt_destinos->execute();
$destinos = $stmt_destinos->fetchAll(PDO::FETCH_ASSOC);

$stmt_precios = $pdo->prepare("SELECT id, nombre, precio FROM precios_hyt WHERE en_venta = 1");
$stmt_precios->execute();
$productos = $stmt_precios->fetchAll(PDO::FETCH_ASSOC);

// Obtener el hyt_admin asignado a esta agencia
$stmt_admin = $pdo->prepare("SELECT hyt_admin_id FROM hyt_admin_agencia WHERE hyt_agencia_id = ?");
$stmt_admin->execute([$agencia_id]);
$hyt_admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);
$hyt_admin_id = $hyt_admin['hyt_admin_id'];

// Lógica para el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['realizar_pedido'])) {
    $destino_id = $_POST['destino'];
    $hora_salida = $_POST['hora_salida'];
    $interno = $_POST['interno'];
    $observaciones = $_POST['observaciones'];
    $fecha_pedido = date('Y-m-d');
    $estado = 'vigente';

    // Insertar en pedidos_hyt
    $stmt_pedido = $pdo->prepare("INSERT INTO pedidos_hyt (nombre_agencia, correo_electronico_agencia, fecha_pedido, estado, interno, hora_salida, destino_id, hyt_admin_id, observaciones) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt_pedido->execute([$nombre_agencia, $correo_agencia, $fecha_pedido, $estado, $interno, $hora_salida, $destino_id, $hyt_admin_id, $observaciones])) {
        $errorInfo = $stmt_pedido->errorInfo();
        echo "Error al realizar el pedido: " . $errorInfo[2];
        exit();
    } else {
        $pedido_id = $pdo->lastInsertId(); 

        // Insertar detalle del pedido
        $stmt_detalle = $pdo->prepare("INSERT INTO detalle_pedidos_hyt (pedido_id, nombre, precio, cantidad) VALUES (?, ?, ?, ?)");
        foreach ($_POST['productos'] as $producto_id => $cantidad) {
            if ($cantidad > 0) {
                $stmt_producto = $pdo->prepare("SELECT nombre, precio FROM precios_hyt WHERE id = ?");
                $stmt_producto->execute([$producto_id]);
                $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);
                if (!$stmt_detalle->execute([$pedido_id, $producto['nombre'], $producto['precio'], $cantidad])) {
                    $errorInfo = $stmt_detalle->errorInfo();
                    echo "Error al insertar el detalle del pedido: " . $errorInfo[2];
                    exit();
                }
            }
        }

        // Enviar correo
        $subject = "Detalle del Pedido Realizado";
        $message = "Se ha realizado un pedido con los siguientes detalles:\n\n";
        foreach ($_POST['productos'] as $producto_id => $cantidad) {
            if ($cantidad > 0) {
                $stmt_producto = $pdo->prepare("SELECT nombre FROM precios_hyt WHERE id = ?");
                $stmt_producto->execute([$producto_id]);
                $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);
                $message .= "{$producto['nombre']}: $cantidad\n";
            }
        }

        if (!enviarCorreo($correo_agencia, $subject, $message)) {
            echo "Error al enviar el correo.";
        } else {
            echo "Pedido realizado correctamente y correo enviado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Realizar Pedido</title>
    <link rel="stylesheet" href="../css/hyt_variables.css">
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

        <label for="hora_salida">Hora de salida:</label>
        <input type="time" id="hora_salida" name="hora_salida" required>

        <label for="observaciones">Observaciones:</label>
        <textarea id="observaciones" name="observaciones" rows="4" cols="50" placeholder="Escriba cualquier observación sobre el pedido"></textarea>

        <h2>Detalle del pedido</h2>

        <?php foreach ($productos as $producto): ?>
            <label for="producto_<?php echo $producto['id']; ?>"><?php echo $producto['nombre']; ?> (Precio: <?php echo $producto['precio']; ?>):</label>
            <input type="number" id="producto_<?php echo $producto['id']; ?>" name="productos[<?php echo $producto['id']; ?>]" min="0" value="0">
        <?php endforeach; ?>

        <button type="submit" name="realizar_pedido">Realizar Pedido</button>
    </form>

</body>
</html>
