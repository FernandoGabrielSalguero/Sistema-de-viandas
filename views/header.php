<?php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Administrador') {
    header("Location: ../views/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Admin Panel - Viandas</title>
</head>
<body>
    <div class="header">
        <h1>Panel de Administrador</h1>
        <nav>
            <button onclick="location.href='admin_dashboard.php'">Dashboard</button>
            <button onclick="location.href='manage_menus.php'">Gestión de Menús</button>
            <button onclick="location.href='manage_users.php'">Gestión de Usuarios</button>
            <button onclick="location.href='manage_orders.php'">Gestión de Pedidos</button>
            <button onclick="location.href='manage_colegios.php'">Colegios</button>
            <button onclick="location.href='manage_cursos.php'">Cursos</button>
            <button onclick="location.href='../php/logout.php'">Logout</button>
        </nav>
    </div>
