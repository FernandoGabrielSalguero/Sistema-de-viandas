<?php
// add_menu.php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $fecha = $_POST['fecha'];

    // Preparar y ejecutar la consulta SQL
    $sql = "INSERT INTO menus (nombre, precio, fecha) VALUES ('$nombre', '$precio', '$fecha')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ../views/admin_dashboard.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
