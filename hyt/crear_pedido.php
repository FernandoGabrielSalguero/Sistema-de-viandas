<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_agencia') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';
include 'header_hyt_agencia.php';

// Obtener los destinos disponibles para el menú desplegable
$stmt_destinos = $pdo->prepare("SELECT id, nombre FROM destinos_hyt");
$stmt_destinos->execute();
$destinos = $stmt_destinos->fetchAll(PDO::FETCH_ASSOC);

// Obtener el hyt_admin asignado a esta agencia
$agencia_id = $_SESSION['usuario_id'];
$stmt_admin = $pdo->prepare("SELECT hyt_admin_id FROM hyt_admin_agencia WHERE hyt_agencia_id = ?");
$stmt_admin->execute([$agencia_id]);
$hyt_admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);
$hyt_admin_id = $hyt_admin['hyt_admin_id'];

// Procesar el formulario de pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['realizar_pedido'])) {
    $destino_id = $_POST['destino'];
    $hora_salida = $_POST['hora_salida'];
    $fecha_pedido = date('Y-m-d');  // Fecha actual
    $estado = 'vigente'; // El pedido comienza como "vigente"
    
    // Insertar el pedido en la tabla pedidos_hyt
    $stmt_pedido = $pdo->prepare("INSERT INTO pedidos_hyt (nombre_agencia, fecha_pedido, hora_salida, estado, destino_id, hyt_admin_id) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt_pedido->execute([$agencia_id, $fecha_pedido, $hora_salida, $estado, $destino_id, $hyt_admin_id])) {
        $pedido_id = $pdo->lastInsertId(); // Obtener el ID del pedido recién creado
        
        // Insertar el detalle del pedido en la tabla detalle_pedidos_hyt
        $stmt_detalle = $pdo->prepare("INSERT INTO detalle_pedidos_hyt (pedido_id, desayuno_dia_siguiente, almuerzo_caliente, media_tarde, refrigerio_sandwich_almuerzo, cena_caliente, refrigerio_sandwich_cena, desayuno_noche, sandwich_noche)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt_detalle->execute([
            $pedido_id,
            $_POST['desayuno_dia_siguiente'],
            $_POST['almuerzo_caliente'],
            $_POST['media_tarde'],
            $_POST['refrigerio_sandwich_almuerzo'],
            $_POST['cena_caliente'],
            $_POST['refrigerio_sandwich_cena'],
            $_POST['desayuno_noche'],
            $_POST['sandwich_noche']
        ])) {
            $success = "Pedido realizado correctamente.";
        } else {
            $error = "Error al insertar el detalle del pedido.";
        }
    } else {
        $error = "Error al realizar el pedido.";
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

<form method="POST" action="crear_pedido.php">
    <label for="destino">Seleccionar destino:</label>
    <select id="destino" name="destino" required>
        <?php foreach ($destinos as $destino): ?>
            <option value="<?php echo $destino['id']; ?>"><?php echo $destino['nombre']; ?></option>
        <?php endforeach; ?>
    </select>

    <label for="hora_salida">Hora de salida:</label>
    <input type="time" id="hora_salida" name="hora_salida" required>

    <h2>Detalle del pedido</h2>

    <label for="desayuno_dia_siguiente">Desayuno día siguiente:</label>
    <input type="number" id="desayuno_dia_siguiente" name="desayuno_dia_siguiente" min="0" value="0">

    <label for="almuerzo_caliente">Almuerzo caliente:</label>
    <input type="number" id="almuerzo_caliente" name="almuerzo_caliente" min="0" value="0">

    <label for="media_tarde">Media tarde:</label>
    <input type="number" id="media_tarde" name="media_tarde" min="0" value="0">

    <label for="refrigerio_sandwich_almuerzo">Refrigerio sandwich almuerzo:</label>
    <input type="number" id="refrigerio_sandwich_almuerzo" name="refrigerio_sandwich_almuerzo" min="0" value="0">

    <label for="cena_caliente">Cena caliente:</label>
    <input type="number" id="cena_caliente" name="cena_caliente" min="0" value="0">

    <label for="refrigerio_sandwich_cena">Refrigerio sandwich cena:</label>
    <input type="number" id="refrigerio_sandwich_cena" name="refrigerio_sandwich_cena" min="0" value="0">

    <label for="desayuno_noche">Desayuno noche:</label>
    <input type="number" id="desayuno_noche" name="desayuno_noche" min="0" value="0">

    <label for="sandwich_noche">Sandwich noche:</label>
    <input type="number" id="sandwich_noche" name="sandwich_noche" min="0" value="0">

    <button type="submit" name="realizar_pedido">Realizar Pedido</button>
</form>

</body>
</html>
