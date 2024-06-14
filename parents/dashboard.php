<?php
include '../common/session.php';
check_login();

if ($_SESSION['role'] !== 'parent') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Parent Dashboard</h1>
    <p>Welcome, Parent!</p>
    <!-- Aquí añadiremos más funcionalidades específicas para los padres -->
</body>
</html>
