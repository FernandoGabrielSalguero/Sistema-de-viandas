<?php
// login.php
include 'db.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['username'];
    $contraseña = $_POST['password'];

    // Escapar caracteres para prevenir inyección SQL
    $usuario = $conn->real_escape_string($usuario);
    $contraseña = $conn->real_escape_string($contraseña);

    // Consultar la base de datos
    $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario' AND contraseña = '$contraseña'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['userid'] = $row['id'];
        $_SESSION['username'] = $row['usuario'];
        $_SESSION['role'] = $row['rol'];

        if ($row['rol'] == 'Administrador') {
            header("Location: ../views/admin_dashboard.php");
        } elseif ($row['rol'] == 'Usuario') {
            header("Location: ../views/user_dashboard.php");
        }
    } else {
        echo "Usuario o contraseña incorrectos.";
    }
}
