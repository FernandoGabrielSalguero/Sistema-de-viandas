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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <title>Dashboard</title>
</head>
<body>
    <header class="main-header">
        <div class="user-info">
            <h1>Que gusto verte de nuevo, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <nav class="main-nav">
            <button onclick="window.location.href='../admin/dashboard.php'">Home</button>
            <button onclick="window.location.href='../admin/schools.php'">Manage Schools</button>
            <button onclick="window.location.href='../admin/courses.php'">Manage Courses</button>
            <button onclick="window.location.href='../logout.php'">Logout</button>
        </nav>
    </header>
