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
    die(json_encode([
        "status" => "error",
        "message" => "Conexión fallida: " . $conn->connect_error
    ]));
}

function handle_error($errno, $errstr, $errfile, $errline) {
    $error_message = "[$errno] $errstr - $errfile:$errline";
    echo json_encode([
        "status" => "error",
        "message" => $error_message
    ]);
    die();
}

set_error_handler("handle_error");
