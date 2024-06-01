<?php
// add_user.php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    // Preparar y ejecutar la consulta SQL
    $sql = "INSERT INTO usuarios (nombre, apellido, usuario, contraseña, telefono, correo, rol) 
            VALUES ('$nombre', '$apellido', '$usuario', '$contraseña', '$telefono', '$correo', '$rol')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ../views/manage_users.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
