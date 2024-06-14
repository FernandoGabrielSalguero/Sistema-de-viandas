<?php
include '../common/session.php';
check_login();

if ($_SESSION['role'] !== 'kitchen') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Kitchen Dashboard</h1>
    <p>Welcome, Kitchen Staff!</p>
    <!-- Aquí añadiremos más funcionalidades específicas para el personal de cocina -->
</body>
</html>
