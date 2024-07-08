<?php
session_start();
if ($_SESSION['rol'] !== 'administrador') {
    header('Location: /login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
</head>
<body>
    <h1>Bienvenido al Dashboard del Administrador</h1>
    <!-- Contenido del dashboard administrador aquí -->
</body>
</html>
