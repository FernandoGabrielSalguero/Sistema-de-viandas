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
    <link rel="stylesheet" href="../styles.css">
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
                    <!-- <li><button onclick="window.location.href='../admin/dashboard.php'">Inicio</button></li> -->
                    <?php if ($_SESSION['role'] === 'admin') : ?>
                        <li><button onclick="window.location.href='../admin/dashboard.php'">Inicio</button></li>
                        <li><button onclick="window.location.href='../admin/courses.php'">Gestionar Cursos</button></li>
                        <li><button onclick="window.location.href='../admin/school_profile.php'">Gestionar Colegios</button></li>
                        <li><button onclick="window.location.href='../admin/parents.php'">Gestionar Padres y Hijos</button></li>
                        <li><button onclick="window.location.href='../admin/create_menu.php'">Gestionar Menús</button></li>
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
