<?php
session_start();
require_once '../config/database.php';

// Comprobación básica de POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Conectar a la base de datos
    $db = new Database();
    $conn = $db->getConnection();

    // Preparar consulta
    $stmt = $conn->prepare("SELECT Id, Contraseña, Rol FROM Usuarios WHERE Usuario = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Contraseña'])) {
            // Autenticación exitosa, establecer sesión
            $_SESSION['user_id'] = $user['Id'];
            $_SESSION['role'] = $user['Rol'];
            header("Location: dashboard.php");
            exit();
        }
    }
    // Fallo en la autenticación
    echo "<script>alert('Usuario o contraseña incorrectos');</script>";
}