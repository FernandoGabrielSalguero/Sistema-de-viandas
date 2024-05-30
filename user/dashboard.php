<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Usuario') {
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Usuario</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Dashboard - Usuario</h1>
        <ul>
            <li><a href="seleccionar_vianda.php">Seleccionar Viandas</a></li>
            <li><a href="historial_pedidos.php">Historial de Pedidos</a></li>
            <li><a href="cancelar_vianda.php">Cancelar Vianda</a></li>
            <li><a href="../logout.php">Cerrar Sesión</a></li>
        </ul>
    </div>
</body>
</html>
