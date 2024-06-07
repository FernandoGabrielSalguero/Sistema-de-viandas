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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: red;
            color: white;
            padding: 10px 20px;
        }
        .header-content h1 {
            margin: 0;
            padding-bottom: 10px;
        }
        .navigation {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        button {
            background-color: blue;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: darkblue;
        }
        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>¡Qué gusto verte de nuevo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <div class="navigation">
                <button onclick="location.href='../dashboard/admin.php'">Dashboard</button>
                <button onclick="location.href='../views/manage_users.php'">Gestionar Usuarios</button>
                <button onclick="location.href='../views/gestionar_colegios.php'">Gestionar Colegios</button>
                <button onclick="location.href='../php/manage_menus.php'">Gestionar Menús</button>
                <button onclick="location.href='../php/view_orders.php'">Ver Pedidos</button>
                <button onclick="location.href='../php/financial_reports.php'">Reportes Financieros</button>
                <button onclick="location.href='../php/logout.php'">Cerrar Sesión</button>
            </div>
        </div>
    </header>
    <div class="main-content">
