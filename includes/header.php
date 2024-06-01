<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="header">
        <h1>Panel de Control - Administrador</h1>
        <a href="../logout.php">Cerrar Sesión</a>
    </div>
