<?php
session_start();
include '../includes/header_papas.php';
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'papas') {
    header("Location: ../index.php");
    exit();
}

// Obtener información del usuario
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT Nombre, Correo, Saldo FROM Usuarios WHERE Id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Papás</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($usuario['Nombre']); ?></h1>
    <p>Correo: <?php echo htmlspecialchars($usuario['Correo']); ?></p>
    <p>Saldo disponible: <?php echo number_format($usuario['Saldo'], 2); ?> ARS</p>
</body>
</html>
