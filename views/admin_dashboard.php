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
    <title>Dashboard - Administrador</title>
    <style>
        .header nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .header nav button {
            flex: 1 1 calc(25% - 10px);
            margin: 5px;
            padding: 10px;
            font-size: 14px;
        }
        .kpi-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 20px;
        }
        .kpi-card {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin: 10px;
            text-align: center;
            flex: 1 1 calc(33% - 20px);
        }
        .kpi-card h3 {
            margin: 0;
        }
        .kpi-card p {
            font-size: 24px;
            margin: 10px 0 0;
        }
        .filter-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .filter-container input {
            margin: 0 10px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard - Administrador</h1>
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
    <div class="container">
        <div class="filter-container">
            <label for="start-date">Desde:</label>
            <input type="date" id="start-date">
            <label for="end-date">Hasta:</label>
            <input type="date" id="end-date">
            <button onclick="fetchDashboardData()">Filtrar</button>
        </div>
        <div class="kpi-container" id="kpi-container">
            <div class="kpi-card">
                <h3>Dinero en Pedidos Aprobados</h3>
                <p id="total-aprobado">$0</p>
            </div>
            <div class="kpi-card">
                <h3>Dinero en Pedidos en Espera</h3>
                <p id="total-espera">$0</p>
            </div>
            <div class="kpi-card">
                <h3>Total de Viandas Pedidas</h3>
                <p id="total-viandas">0</p>
            </div>
            <div class="kpi-card">
                <h3>Total de Usuarios</h3>
                <p id="total-usuarios">0</p>
            </div>
            <div class="kpi-card">
                <h3>Usuarios con Pedidos</h3>
                <p id="usuarios-con-pedidos">0</p>
            </div>
            <div class="kpi-card">
                <h3>Usuarios sin Pedidos</h3>
                <p id="usuarios-sin-pedidos">0</p>
            </div>
            <div class="kpi-card">
                <h3>Total de Devoluciones</h3>
                <p id="total-devoluciones">0</p>
            </div>
        </div>
    </div>
    <script>
        async function fetchDashboardData() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            let url = '../php/admin_dashboard_data.php';
            if (startDate && endDate) {
                url += `?start_date=${startDate}&end_date=${endDate}`;
            }
            const response = await fetch(url);
            const data = await response.json();
            document.getElementById('total-aprobado').textContent = `$${parseFloat(data.total_aprobado).toFixed(2)}`;
            document.getElementById('total-espera').textContent = `$${parseFloat(data.total_espera).toFixed(2)}`;
            document.getElementById('total-viandas').textContent = data.total_viandas;
            document.getElementById('total-usuarios').textContent = data.total_usuarios;
            document.getElementById('usuarios-con-pedidos').textContent = data.usuarios_con_pedidos;
            document.getElementById('usuarios-sin-pedidos').textContent = data.usuarios_sin_pedidos;
            document.getElementById('total-devoluciones').textContent = data.total_devoluciones;
        }

        document.addEventListener('DOMContentLoaded', fetchDashboardData);
    </script>
</body>
</html>
