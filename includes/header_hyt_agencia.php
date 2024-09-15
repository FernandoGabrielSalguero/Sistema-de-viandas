<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>HYT Agencia - Dashboard</title>
    <style>
        /* Estilos del encabezado */
        .header {
            background-color: #f8f9fa;
            padding: 10px 20px;
            text-align: center;
        }

        .header .nav {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .header .nav a {
            text-decoration: none;
            padding: 10px 20px;
            color: white;
            background-color: #28a745;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }

        .header .nav a.logout {
            background-color: #dc3545;
        }

        .header .nav a:hover {
            opacity: 0.9;
        }

        .header .nav a.active {
            background-color: #007bff;
        }
    </style>
</head>
<body>
<header>
    <nav>
        <ul>
            <li><a href="dashboard_hyt_agencia.php">Dashboard</a></li>
            <li><a href="crear_pedido.php" class="button">Crear nuevo pedido</a></li>
            <li><a href="modificar_pedidos_hyt.php" class="button">Editar pedidos</a></li>
            <li><a href="logout.php">Salir</a></li>
        </ul>
    </nav>
</header>
</body>
</html>
