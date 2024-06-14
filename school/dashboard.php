<?php
include '../common/session.php';
check_login();

if ($_SESSION['role'] !== 'school') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>School Dashboard</h1>
    <p>Welcome, School Manager!</p>
    <!-- Aquí añadiremos más funcionalidades específicas para el encargado del colegio -->
</body>
</html>
