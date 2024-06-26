<?php
include 'Common/db_connect.php';

$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM usuarios WHERE username='$username' AND password='$password'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    session_start();
    $_SESSION['username'] = $row['username'];
    $_SESSION['role'] = $row['role'];

    switch ($row['role']) {
        case 'Administrador':
            header('Location: Admin/admin_dashboard.php');
            break;
        case 'Usuario':
            header('Location: Usuarios/usuario_dashboard.php');
            break;
        case 'Cocina':
            header('Location: Cocina/cocina_dashboard.php');
            break;
        case 'School Leader':
            header('Location: School Leader/school_leader_dashboard.php');
            break;
        default:
            header('Location: login.php');
            break;
    }
    exit();
} else {
    echo "<script>alert('Usuario o contraseña incorrectos');</script>";
    header('Location: login.php');
    exit();
}
