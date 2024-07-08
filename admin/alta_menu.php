<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Procesar el formulario cuando se envíe
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_menu = $_POST['nombre_menu'];
    $fecha_entrega = $_POST['fecha_entrega'];
    $fecha_hora_compra = $_POST['fecha_hora_compra'];
    $fecha_hora_cancelacion = $_POST['fecha_hora_cancelacion'];
    $precio = $_POST['precio'];
    $estado = $_POST['estado'];

    // Validar que todos los campos estén llenos
    if (empty($nombre_menu) || empty($fecha_entrega) || empty($fecha_hora_compra) || empty($fecha_hora_cancelacion) || empty($precio) || empty($estado)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Insertar el nuevo menú en la base de datos
        $stmt = $pdo->prepare("INSERT INTO Menú (Nombre, Fecha_entrega, Fecha_hora_compra, Fecha_hora_cancelacion, Precio, Estado) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$nombre_menu, $fecha_entrega, $fecha_hora_compra, $fecha_hora_cancelacion, $precio, $estado])) {
            $success = "Menú creado con éxito.";
        } else {
            $error = "Hubo un error al crear el menú.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Menú</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Alta de Menú</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <form method="post" action="alta_menu.php">
        <label for="nombre_menu">Nombre del Menú</label>
        <input type="text" id="nombre_menu" name="nombre_menu" required>
        
        <label for="fecha_entrega">Fecha de Entrega</label>
        <input type="date" id="fecha_entrega" name="fecha_entrega" required>
        
        <label for="fecha_hora_compra">Fecha y Hora Límite de Compra</label>
        <input type="datetime-local" id="fecha_hora_compra" name="fecha_hora_compra" required>
        
        <label for="fecha_hora_cancelacion">Fecha y Hora Límite de Cancelación</label>
        <input type="datetime-local" id="fecha_hora_cancelacion" name="fecha_hora_cancelacion" required>
        
        <label for="precio">Precio</label>
        <input type="number" id="precio" name="precio" step="0.01" required>
        
        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
            <option value="En venta">En venta</option>
            <option value="Sin stock">Sin stock</option>
        </select>
        
        <button type="submit">Crear Menú</button>
    </form>
</body>
</html>
