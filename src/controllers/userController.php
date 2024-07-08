<?php
session_start();
require_once '../config/database.php';

// Obtener la acción del formulario
$action = $_POST['action'] ?? '';

if ($action == 'login') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = authenticateUser($username, $password);
    if ($user) {
        // Establecer variables de sesión
        $_SESSION['user_id'] = $user['Id'];
        $_SESSION['username'] = $user['Usuario'];
        $_SESSION['role'] = $user['Rol'];

        // Redirigir al dashboard correspondiente
        header('Location: ../views/dashboardAdmin.php');  // Ajusta según el rol y la estructura del proyecto
        exit();
    } else {
        // Redirigir a login con error
        header('Location: ../views/login.php?error=loginFailed');
        exit();
    }
}

function authenticateUser($username, $password) {
    $pdo = getDB();
    $sql = "SELECT Id, Usuario, Contrasena, Rol FROM Usuarios WHERE Usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['Contrasena'])) {
        return $user;
    }
    return false;
}
