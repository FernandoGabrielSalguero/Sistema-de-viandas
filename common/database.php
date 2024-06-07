<?php
$host = '127.0.0.1:3306';  
$dbname = 'u437094107_viandas_sch00l';  
$username = 'u437094107_adm111n';  
$password = '9t:RuQ7^nr+/';  

function getDatabaseConnection() {
    global $host, $dbname, $username, $password;

    // Crear una nueva conexión PDO
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        // Establecer el modo de error de PDO a excepción
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("ERROR: Could not connect. " . $e->getMessage());
    }
}
