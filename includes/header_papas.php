<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Papás</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        nav {
            background-color: #f44336; /* Rojo */
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
            color: white; /* Letra blanca */
            background-color: #4caf50; /* Verde */
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            display: block;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        nav ul li a:hover {
            background-color: #388e3c; /* Verde oscuro */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            nav ul li {
                flex: 1 1 calc(50% - 10px);
                margin: 5px;
            }
        }

        @media (max-width: 480px) {
            nav ul li {
                flex: 1 1 100%;
                margin: 5px;
            }
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="dashboard.php">Inicio</a></li>
            <li><a href="cargar_saldo.php">Cargar Saldo</a></li>
            <li><a href="comprar_viandas.php">Comprar Viandas</a></li>
            <li><a href="../papas/logout.php">Salir</a></li>
        </ul>
    </nav>
</body>
</html>
