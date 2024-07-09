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
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        nav ul {
            list-style: none;
            padding: 0;
        }

        nav ul li {
            position: relative;
        }

        nav ul li a {
            text-decoration: none;
            display: block;
            padding: 10px;
            color: #000;
        }

        nav ul li ul {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            list-style: none;
            padding: 0;
        }

        nav ul li:hover > ul {
            display: block;
        }

        nav ul li ul li a {
            padding: 10px;
        }

        nav ul li ul li a:hover {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li>
                <a href="#">Gestión de Pedidos</a>
                <ul>
                    <li><a href="gestion_pedidos.php">Gestión Pedidos</a></li>
                    <li><a href="gestion_saldo.php">Gestión Saldo</a></li>
                </ul>
            </li>
            <li>
                <a href="#">Usuarios</a>
                <ul>
                    <li><a href="alta_usuarios.php">Alta Usuarios</a></li>
                    <li><a href="asignar_hijos.php">Gestión Usuarios</a></li>
                </ul>
            </li>
            <li><a href="gestion_representantes.php">Gestión Representantes</a></li>
            <li><a href="alta_colegios.php">Alta Colegios</a></li>
            <li><a href="gestion_colegios.php">Gestión Colegios</a></li>
            <li><a href="alta_menu.php">Alta Menú</a></li>
            <li><a href="alta_preferencias.php">Alta Preferencias Alimenticias</a></li>
            <li><a href="logout.php">Salir</a></li>
        </ul>
    </nav>
</body>
</html>
