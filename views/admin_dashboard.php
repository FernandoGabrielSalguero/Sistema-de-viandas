<?php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Administrador') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Admin Dashboard - Viandas</title>
</head>
<body>
    <div class="header">
        <h1>Panel de Administrador</h1>
        <a href="../php/logout.php">Logout</a>
    </div>
    <div class="container">
        <h2>Bienvenido, <?php echo $_SESSION['username']; ?></h2>
        <ul>
            <li><a href="manage_menus.php">Gestión de Menús</a></li>
            <!-- Agregar más enlaces aquí según sea necesario -->
        </ul>
    </div>
</body>
</html>
