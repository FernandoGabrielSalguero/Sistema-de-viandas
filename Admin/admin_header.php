<?php
if ($_SESSION['role'] != 'Administrador') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Encabezado Admin</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header class="admin-header">
        <div class="header-content">
            <div class="header-title">Panel de Administración</div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="registration_users.php">Gestión de Usuarios</a></li>
                    <li><a href="school_management.php">Gestión de Colegios</a></li>
                    <li><a href="options_menu.php">Gestión de Menús</a></li>
                    <li><a href="order_viewer.php">Gestión de Pedidos</a></li>
                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>
</body>
</html>
