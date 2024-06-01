<?php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Usuario') {
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
    <title>User Dashboard - Viandas</title>
</head>
<body>
    <div class="header">
        <h1>Panel de Usuario</h1>
        <a href="../php/logout.php">Logout</a>
    </div>
    <div class="container">
        <h2>Bienvenido, <?php echo $_SESSION['username']; ?></h2>
        <!-- Contenido específico para el usuario -->
    </div>
</body>
</html>
