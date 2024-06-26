<?php
$host = '127.0.0.1:3306';
$dbname = 'u437094107_viandas_sch00l';
$username = 'u437094107_adm111n';
$password = '9t:RuQ7^nr+/';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    $error_message = "Conexión fallida: " . $conn->connect_error;
    echo "<script>console.error('".$error_message."');</script>";
    die($error_message);
}

echo "<script>console.log('Conexión exitosa a la base de datos');</script>";
