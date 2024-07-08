<?php
session_start();
include 'config/database.php';

// Conexión a la base de datos
$db = new Database();
$conn = $db->getConnection();

$usuario = $_POST['usuario'];
$contraseña = hash('sha256', $_POST['contraseña']); // Encriptación SHA-256

// Consulta a la base de datos
$sql = "SELECT id, rol FROM usuarios WHERE usuario = ? AND contraseña = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $usuario, $contraseña);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $_SESSION['usuario_id'] = $row['id'];
    $_SESSION['rol'] = $row['rol'];

    // Redirección por rol
    switch ($row['rol']) {
        case 'administrador':
            header('Location: /templates/admin_dashboard.php');
            break;
        case 'papas':
            header('Location: /templates/papas_dashboard.php');
            break;
        case 'cocina':
            header('Location: /templates/cocina_dashboard.php');
            break;
        case 'representante':
            header('Location: /templates/representante_dashboard.php');
            break;
        default:
            header('Location: /login.php?error=rol_desconocido');
            break;
    }
    exit();
} else {
    header('Location: /login.php?error=credenciales_invalidas');
    exit();
}
