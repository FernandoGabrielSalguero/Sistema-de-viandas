<?php
include 'session.php';
check_login();
include 'db_connect.php';

// Obtener los datos del usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .main-header {
            background-color: #f4f4f4;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: auto;
            padding: 0 20px;
        }

        .user-info h1 {
            margin: 0;
            font-size: 24px;
        }

        .user-info p {
            margin: 5px 0 0;
            color: #555;
        }

        .main-nav {
            position: relative;
        }

        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 10px;
        }

        .nav-links li {
            display: inline;
        }

        .nav-links button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        .nav-links button:hover {
            background: #0056b3;
        }

        .menu-icon {
            display: none;
            font-size: 30px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
                flex-direction: column;
                gap: 0;
                background: #f4f4f4;
                position: absolute;
                top: 60px;
                left: 0;
                width: 100%;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .nav-links.nav-active {
                display: flex;
            }

            .nav-links button {
                width: 100%;
                text-align: left;
                padding: 15px;
                border-bottom: 1px solid #ddd;
            }

            .nav-links button:last-child {
                border-bottom: none;
            }

            .menu-icon {
                display: block;
            }
        }
    </style>
    <title>Dashboard</title>
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="user-info">
                <h1>¡Qué gusto verte de nuevo, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <nav class="main-nav">
                <ul class="nav-links">
                    <?php if ($_SESSION['role'] === 'admin') : ?>
                        <li><button onclick="window.location.href='../admin/dashboard.php'">Inicio</button></li>
                        <li><button onclick="window.location.href='../admin/parents.php'">Gestionar Padres y Hijos</button></li>
                        <li><button onclick="window.location.href='../admin/school_profile.php'">Gestionar Colegios</button></li>
                        <li><button onclick="window.location.href='../admin/courses.php'">Gestionar Cursos</button></li>
                        <li><button onclick="window.location.href='../admin/create_menu.php'">Gestionar Menús</button></li>
                        <li><button onclick="window.location.href='../admin/schools.php'">Escuelas</button></li>
                        <li><button onclick="window.location.href='../admin/create_user.php'">Crear Usuario</button></li>
                        <li><button onclick="window.location.href='../admin/verify_recharge.php'">Verificar Recargas</button></li>
                    <?php elseif ($_SESSION['role'] === 'parent') : ?>
                        <li><button onclick="window.location.href='../parents/dashboard.php'">Inicio</button></li>
                        <li><button onclick="window.location.href='../parents/recharge.php'">Recargar Saldo</button></li>
                        <li><button onclick="window.location.href='../parents/order_menu.php'">Pedir viandas</button></li>
                    <?php elseif ($_SESSION['role'] === 'kitchen') : ?>
                        <li><button onclick="window.location.href='../kitchen/kitchen_dashboard.php'">Dashboard de Cocina</button></li>
                    <?php elseif ($_SESSION['role'] === 'school') : ?>
                        <li><button onclick="window.location.href='../school/school_rep_profile.php'">Pedidos</button></li>
                    <?php endif; ?>
                    <li><button onclick="window.location.href='../logout.php'">Cerrar Sesión</button></li>
                </ul>
                <div class="menu-icon" onclick="toggleMenu()">
                    &#9776; <!-- Ícono de menú -->
                </div>
            </nav>
        </div>
    </header>

    <script>
        function toggleMenu() {
            const nav = document.querySelector('.nav-links');
            nav.classList.toggle('nav-active');
        }
    </script>
</body>
</html>
