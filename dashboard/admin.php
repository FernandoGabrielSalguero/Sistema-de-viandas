<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador | Viandas</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<?php include 'header_admin.php'; ?>
    <h1>Dashboard del Administrador</h1>
    <div>
        <h2>Control de Usuarios</h2>
        <button onclick="location.href='manage_users.php'">Gestionar Usuarios</button>
        <h2>Control de Menús</h2>
        <button onclick="location.href='manage_menus.php'">Gestionar Menús</button>
        <h2>Vista de Pedidos</h2>
        <button onclick="location.href='view_orders.php'">Ver Pedidos</button>
        <h2>Reportes Financieros</h2>
        <button onclick="location.href='financial_reports.php'">Ver Reportes Financieros</button>
    </div>
    <script src="../js/functions.js"></script>
</body>
</html>
