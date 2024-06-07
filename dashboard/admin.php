<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: /index.php');
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Administrador</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <h1>Dashboard Administrador</h1>
    <ul>
        <li><a href="manage_users.php">Gestionar Usuarios</a></li>
        <li><a href="manage_menus.php">Gestionar Menús</a></li>
        <li><a href="report.php">Ver Reportes</a></li>
    </ul>
</body>
</html>
