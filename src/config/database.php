<?php
function getDB() {
    $host = '127.0.0.1:3306';  // Ajusta según tu configuración
    $dbname = 'u437094107_viandas_sch00l';  // Nombre de tu base de datos
    $username = 'u437094107_adm111n';  // Usuario de la base de datos
    $password = '9t:RuQ7^nr+/';  // Contraseña del usuario de la base de datos

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        exit("Error de conexión: " . $e->getMessage());
    }
}
