<?php
include '../common/session.php';
check_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Admin Dashboard</h1>
    <p>Welcome, Admin!</p>
    <nav>
        <ul>
            <li><a href="schools.php">Escuelas</a></li>
            <li><a href="courses.php">Cursos</a></li>
            <!-- Agrega más enlaces según las funcionalidades disponibles -->
        </ul>
    </nav>
</body>
</html>
