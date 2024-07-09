<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: ../index.php");
    exit();
}

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Bienvenido al Dashboard de Administrador";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
</head>
<body>
    <h1>Dashboard Administrador</h1>
    <p>Contenido del dashboard aquí...</p>
</body>
</html>
