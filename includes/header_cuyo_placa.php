<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Cuyo Placa</title>
    <style>
        nav {
            background-color: #343a40;
            padding: 10px;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        nav ul li {
            margin: 5px 0;
        }

        nav ul li a {
            text-decoration: none;
            color: white;
            background-color: #007bff;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            display: block;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        nav ul li a:hover {
            background-color: #0056b3;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="dashboard_cuyo_placa.php">Inicio</a></li>
            <li><a href="pedidos_viandas_cuyo.php">Crear pedidos</a></li>
            <li><a href="mod_pedidos_viandas_cuyo.php">Modificar pedidos</a></li>
            <li><a href="../admin/logout.php">Salir</a></li>
        </ul>
    </nav>
</body>
</html>
