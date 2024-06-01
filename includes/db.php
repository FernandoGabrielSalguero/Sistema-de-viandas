<?php
$servername = "127.0.0.1:3306";
$username = "u437094107_school_lunch_s";
$password = "N8r!W9cD4NKY";
$dbname = "u437094107_admin2024";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
