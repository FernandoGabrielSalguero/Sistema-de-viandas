<?php
// db.php
$servername = "127.0.0.1:3306";
$username = "u437094107_admin_school_f";
$password = "Pl9?ycuf+5";
$dbname = "u437094107_school_food";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

function handle_error($errno, $errstr, $errfile, $errline) {
    echo "<b>Error:</b> [$errno] $errstr - $errfile:$errline";
    echo "<br>";
    echo "La ejecución del script ha sido detenida.";
    die();
}

set_error_handler("handle_error");
