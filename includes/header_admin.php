<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';
include 'functions.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador</title>
    <style>
        /* Background fondo header */
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            background-color: #f5f5f5;
            padding: 20px;
        }

        nav ul li {
            position: relative;
        }

        nav ul li a {
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            color: #fff;
            border-radius: 5px;
            margin: 5px;
            text-align: center;
            font-weight: bold;
        }

        .basic {
            background-color: #1976d2;
        }

        .tagus {
            background-color: #9575cd;
        }

        .warn {
            background-color: #d32f2f;
        }

        .link {
            background-color: #0d47a1;
        }

        .gray {
            background-color: #cfd8dc;
            color: #000;
        }

        .disabled {
            background-color: #bdbdbd;
            color: #9e9e9e;
        }

        nav ul li a:hover {
            opacity: 0.9;
        }

        nav ul li ul {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            list-style: none;
            padding: 0;
            margin: 0;
            min-width: 200px;
        }

        nav ul li:hover > ul {
            display: block;
        }

        nav ul li ul li a {
            padding: 10px 15px;
            background-color: #1976d2;
            color: #fff;
            border-radius: 0;
        }

        nav ul li ul li a:hover {
            background-color: #007bff;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="dashboard.php" class="tagus">Dashboard</a></li>
            <li>
                <a href="#" class="tagus">Gestión de Pedidos</a>
                <ul>
                    <li><a href="gestion_pedidos.php" class="tagus">Viandas</a></li>
                    <li><a href="gestion_saldo.php" class="tagus">Saldo</a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="tagus">Usuarios</a>
                <ul>
                    <li><a href="alta_usuarios.php" class="tagus">Gestión Usuarios</a></li>
                    <li><a href="agregar_hijo.php" class="tagus">Crear Hijos</a></li>
                    <li><a href="asignar_hijos.php" class="tagus">Asignación Hijos</a></li>
                    <li><a href="alta_preferencias.php" class="tagus">Alta Preferencias Alimenticias</a></li>
                </ul>
            </li>
            <li>
                <a href="#" class="tagus">Colegios</a>
                <ul>
                    <li><a href="alta_colegios.php" class="tagus">Alta Colegios</a></li>
                    <li><a href="gestion_colegios.php" class="tagus">Gestión Colegios</a></li>
                    <li><a href="gestion_representantes.php" class="tagus">Gestión Representantes</a></li>
                </ul>
            </li>
            <li><a href="alta_menu.php" class="tagus">Alta Menú</a></li>
            <li><a href="logout.php" class="warn">Salir</a></li>
        </ul>
    </nav>
</body>
</html>
