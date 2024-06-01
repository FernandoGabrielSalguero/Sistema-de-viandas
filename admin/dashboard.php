<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

// Aquí podrías agregar consultas a la base de datos para obtener información relevante
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Administrador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        /* Añadir estilo básico para el menú lateral y el contenido */
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            padding: 20px;
            color: #fff;
            height: 100vh;
            position: fixed;
        }
        .sidebar a {
            display: block;
            color: #fff;
            padding: 10px 0;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
        }
        .header {
            background-color: #d9534f;
            padding: 10px;
            color: #fff;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_users.php">Gestionar Usuarios</a>
        <a href="manage_menu.php">Gestionar Menú</a>
        <a href="manage_orders.php">Gestionar Pedidos</a>
        <a href="../logout.php">Cerrar Sesión</a>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Bienvenido, Administrador</h1>
        </div>
        <div class="content">
            <!-- Aquí podemos agregar contenido específico del dashboard -->
            <p>Este es el panel de control del administrador.</p>
        </div>
    </div>
</body>
</html>
