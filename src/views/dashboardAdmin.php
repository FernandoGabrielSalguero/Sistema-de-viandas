<?php include 'adminHeader.php'; // Asegúrate de que la ruta al archivo de cabecera sea correcta ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Bienvenido al Dashboard del Administrador</h2>
        <div class="dashboard-widgets">
            <div class="widget">
                <h3>Total Usuarios</h3>
                <p><!-- Número de usuarios --></p>
            </div>
            <div class="widget">
                <h3>Pedidos Hoy</h3>
                <p><!-- Número de pedidos para hoy --></p>
            </div>
            <div class="widget">
                <h3>Saldo Pendiente</h3>
                <p><!-- Saldo total pendiente de aprobación --></p>
            </div>
            <div class="widget">
                <h3>Menús Activos</h3>
                <p><!-- Número de menús actualmente en venta --></p>
            </div>
        </div>
    </div>
</body>
</html>
