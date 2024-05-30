<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../index.php');
    exit;
}

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $fecha = $_POST['fecha'];
    $promocion = $_POST['promocion'];

    $query = "INSERT INTO menu (nombre, precio, fecha, promocion) VALUES ('$nombre', '$precio', '$fecha', '$promocion')";
    if (mysqli_query($conn, $query)) {
        echo "Menú creado con éxito.";
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Menú - Administrador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Crear Menú Semanal</h1>
        <form action="crear_menu.php" method="post">
            <div class="form-group">
                <label for="nombre">Nombre de la Vianda</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="precio">Precio</label>
                <input type="number" step="0.01" id="precio" name="precio" required>
            </div>
            <div class="form-group">
                <label for="fecha">Fecha</label>
                <input type="date" id="fecha" name="fecha" required>
            </div>
            <div class="form-group">
                <label for="promocion">Promoción (%)</label>
                <input type="number" step="0.01" id="promocion" name="promocion" required>
            </div>
            <button type="submit">Crear Menú</button>
        </form>
        <a href="dashboard.php">Volver al Dashboard</a>
    </div>
</body>
</html>