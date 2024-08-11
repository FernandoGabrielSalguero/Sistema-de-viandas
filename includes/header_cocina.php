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

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cocina') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            background-color: #28a745;
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
            background-color: #28a745;
            color: white;
        }

        .warn {
            background-color: #d32f2f;
            color: white;
        }

        .link {
            background-color: #0d47a1;
        }



        nav ul li a:hover {
            opacity: 0.9;
        }

        nav ul li ul {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #285504;
            border: 1px solid #ccc;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            list-style: none;
            padding: 0;
            margin: 0;
            min-width: 200px;
            z-index: 1000;
        }

        nav ul li:hover>ul {
            display: block;
        }

        nav ul li ul li {
            position: relative;
        }

        nav ul li ul li a {
            padding: 10px 15px;
            background-color: #285504;
            color: #fff;
            border-radius: 0;
        }

        nav ul li ul li a:hover {
            background-color: #285504;
        }

        nav ul li ul li ul {
            top: 0;
            left: 100%;
        }
    </style>
</head>

<body>
    <nav>
        <ul>
            <li><a href="pedidos_colegios.php">Colegios</a></li>
            <li><a href="pedidos_cuyo.php">Cuyo Placas</a></li>
            <li><a href="logout.php">Salir</a></li>
        </ul>
    </nav>
</body>

</html>