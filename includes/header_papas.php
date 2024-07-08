<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';
include 'functions.php';

if (!isset($_SESSION['user_id']) || getUserRole($_SESSION['user_id']) !== 'papas') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Papás</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="dashboard.php">Inicio</a></li>
            <li><a href="cargar_saldo.php">Cargar Saldo</a></li>
            <li><a href="historial_pedidos.php">Historial de Pedidos</a></li>
            <li><a href="comprar_viandas.php">Comprar Viandas</a></li>
            <li><a href="../logout.php">Salir</a></li>
        </ul>
    </nav>
