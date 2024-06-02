<?php
include '../php/db.php';  // Asegúrate de que la ruta es correcta y accesible
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Cocina') {
    header("Location: ../views/login.php");
    exit();
}


// Intenta una consulta simple para verificar la conexión a la base de datos
$query = "SELECT 1";
if (!$conn->query($query)) {
    error_log("Failed to execute query: " . $conn->error);
    header("HTTP/1.1 500 Internal Server Error");
    echo "Database connection error. Please contact the system administrator.";
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Cocina</title>
</head>
<body>
    <h1>Bienvenido al Panel de Cocina</h1>
    <!-- Contenido del panel de cocina -->
</body>
</html>
