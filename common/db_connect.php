<?php
$host = '127.0.0.1:3306';
$dbname = 'u437094107_viandas_sch00l';
$username = 'u437094107_adm111n';
$password = '9t:RuQ7^nr+/';

// Habilitar errores (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
