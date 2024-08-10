<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/header_cuyo_placa.php';
include '../includes/db.php';

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cuyo_placa') {
    header("Location: ../index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Cuyo Placa</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Bienvenido al Panel de Cuyo Placa</h1>
    <p>Aquí podrás gestionar las tareas específicas de tu rol.</p>

    <!-- Aquí puedes añadir más contenido o secciones específicas para este dashboard -->
</body>
</html>
