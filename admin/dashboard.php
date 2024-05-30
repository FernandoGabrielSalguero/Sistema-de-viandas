<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administrador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Bienvenido, Administrador</h1>
        <p>Esta es la página de inicio del administrador.</p>
        <a href="crear_menu.php">Crear Menú</a> | 
        <a href="gestionar_usuarios.php">Gestionar Usuarios</a> | 
        <a href="gestionar_pedidos.php">Gestionar Pedidos</a> | 
        <a href="../logout.php">Cerrar Sesión</a>
    </div>
</body>
</html>