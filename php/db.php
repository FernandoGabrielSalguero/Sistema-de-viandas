<?php
$host = '127.0.0.1:3306';
$dbname = 'u437094107_viandas_sch00l';
$username = 'u437094107_adm111n';
$password = '9t:RuQ7^nr+/';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Configuración para que PDO lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ERROR: No se pudo conectar. " . $e->getMessage());
}
