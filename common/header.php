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
        <div class="user-info">
            <h1>¡Qué gusto verte de nuevo, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <nav class="main-nav">
            <button onclick="window.location.href='../admin/dashboard.php'">Inicio</button>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <button onclick="window.location.href='../admin/schools.php'">Gestionar Colegios</button>
                <button onclick="window.location.href='../admin/courses.php'">Gestionar Cursos</button>
                <button onclick="window.location.href='../admin/parents.php'">Gestionar Padres y Hijos</button>
                <button onclick="window.location.href='../admin/verify_recharge.php'">Verificar Recargas</button>
            <?php elseif ($_SESSION['role'] === 'parent'): ?>
                <button onclick="window.location.href='../parents/dashboard.php'">Inicio</button>
                <button onclick="window.location.href='../parents/recharge.php'">Recargar Saldo</button>
                <!-- Agrega más enlaces según las funcionalidades disponibles -->
            <?php endif; ?>
            <button onclick="window.location.href='../logout.php'">Cerrar Sesión</button>
        </nav>
    </header>
