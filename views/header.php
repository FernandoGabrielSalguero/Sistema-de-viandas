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
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="manage_menus.php">Gestión de Menús</a></li>
                <li><a href="manage_users.php">Gestión de Usuarios</a></li>
                <li><a href="../php/logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
