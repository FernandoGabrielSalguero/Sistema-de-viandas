<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php'); // Asegúrate de que la ruta al archivo de login es correcta.
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador | Viandas</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header>
        <div class="header-content">
            <h1>¡Qué gusto verte de nuevo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <div class="navigation">
                <button onclick="location.href='../dashboard/admin.php'">Dashboard</button>
                <button onclick="location.href='../manage_users.php'">Gestionar Usuarios</button>
                <button onclick="location.href='../manage_menus.php'">Gestionar Menús</button>
                <button onclick="location.href='../view_orders.php'">Ver Pedidos</button>
                <button onclick="location.href='../financial_reports.php'">Reportes Financieros</button>
                <button onclick="location.href='../php/logout.php'">Cerrar Sesión</button>
            </div>
        </div>
    </header>
    <div class="main-content">
