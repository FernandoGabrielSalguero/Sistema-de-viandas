<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../php/db.php';
session_start();

echo "Inicio del script<br>"; // Debug

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Cocina') {
    echo "No se ha iniciado sesión o el rol no es Cocina. Redirigiendo...<br>"; // Debug
    header("Location: login.php");
    exit();
}

// Prueba una consulta simple para ver si hay problemas con la base de datos
$sql = "SELECT 1";
$result = $conn->query($sql);
if ($result === FALSE) {
    echo "Error en la consulta SQL: " . $conn->error . "<br>"; // Debug
} else {
    echo "Consulta SQL exitosa: SELECT 1<br>"; // Debug
}

echo "Fin del script<br>"; // Debug
