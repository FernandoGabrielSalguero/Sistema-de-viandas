<?php
include '../Common/db_connect.php';

$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$celular = $_POST['celular'];
$correo = $_POST['correo'];
$usuario = $_POST['usuario'];
$password = $_POST['password'];
$rol = $_POST['rol'];

if ($id) {
    $query = "UPDATE usuarios SET nombre='$nombre', apellido='$apellido', celular='$celular', correo='$correo', usuario='$usuario', password='$password', rol='$rol' WHERE id='$id'";
} else {
    $query = "INSERT INTO usuarios (nombre, apellido, celular, correo, usuario, password, rol) VALUES ('$nombre', '$apellido', '$celular', '$correo', '$usuario', '$password', '$rol')";
}

if ($conn->query($query) === TRUE) {
    // Enviar correo de notificación si es nuevo usuario
    if (!$id) {
        $to = $correo;
        $subject = "Registro de Usuario";
        $body = "Hola $nombre, tu cuenta ha sido creada exitosamente. Usuario: $usuario, Contraseña: $password";
        sendEmail($to, $subject, $body);
    }
    header('Location: registration_users.php');
} else {
    echo "Error: " . $query . "<br>" . $conn->error;
}

$conn->close();
