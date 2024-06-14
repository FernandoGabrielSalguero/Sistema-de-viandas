<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <title>Gestión de Viandas Escolares</title>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>Gestión de Viandas Escolares</h1>
            <nav>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <button onclick="window.location.href='../admin/dashboard.php'">Inicio</button>
                <button onclick="window.location.href='../admin/schools.php'">Gestionar Colegios</button>
                <button onclick="window.location.href='../admin/courses.php'">Gestionar Cursos</button>
                <button onclick="window.location.href='../admin/parents.php'">Gestionar Padres y Hijos</button>
                <button onclick="window.location.href='../admin/create_menu.php'">Gestionar Menús</button>
                <button onclick="window.location.href='../admin/verify_recharge.php'">Verificar Recargas</button>
            <?php elseif ($_SESSION['role'] === 'parent'): ?>
                <button onclick="window.location.href='../parents/dashboard.php'">Inicio</button>
                <button onclick="window.location.href='../parents/recharge.php'">Recargar Saldo</button>
                <button onclick="window.location.href='../parents/order_menu.php'">Realizar Pedido</button>
            <?php endif; ?>
            <button onclick="window.location.href='../logout.php'">Cerrar Sesión</button>
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



