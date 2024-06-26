<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/viandas/Common/db_connect.php';

session_start();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    die("Usuario y contraseña son requeridos.");
}

$query = "SELECT * FROM usuarios WHERE usuario=? AND password=?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['username'] = $row['usuario'];
    $_SESSION['role'] = $row['rol'];

    switch ($row['rol']) {
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
            header('Location: index.php');
            break;
    }
    exit();
} else {
    echo "<script>alert('Usuario o contraseña incorrectos');</script>";
    header('Location: index.php');
    exit();
}

$stmt->close();
$conn->close();