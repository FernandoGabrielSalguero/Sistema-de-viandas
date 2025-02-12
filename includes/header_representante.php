<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';
include 'functions.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'representante') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de representante</title>
</head>
<body>
    <nav>
        <ul>
            <li><a href="dashboard.php">Inicio</a></li>
            <li><a href="gestion_alumnos.php">Gestión Alumnos</a></li>
            <li><a href="logout.php">Salir</a></li>
        </ul>
    </nav>
</body>
</html>
