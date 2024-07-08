<?php
session_start();
require_once '../config/database.php';

$action = $_POST['action'] ?? '';

if ($action == 'login') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Función para autenticar usuario
    $user = authenticateUser($username, $password);
    if ($user) {
        // Establecer variables de sesión
        $_SESSION['user_id'] = $user['Id'];
        $_SESSION['username'] = $user['Usuario'];
        $_SESSION['role'] = $user['Rol'];

        // Redirigir al dashboard correspondiente al rol
        redirectBasedOnRole($user['Rol']);
    } else {
        // Redirigir de nuevo al login con un mensaje de error
        header('Location: ../views/login.php?error=loginFailed');
        exit();
    }
}

function authenticateUser($username, $password) {
    $pdo = getDB();
    $sql = "SELECT * FROM Usuarios WHERE Usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Contrasena'])) {
        return $user;
    }
    return false;
}

function redirectBasedOnRole($role) {
    switch ($role) {
        case 'administrador':
            header('Location: src/views/dashboardAdmin.php');
            break;
        case 'papas':
            header('Location: ../views/dashboardPapas.php');
            break;
        case 'cocina':
            header('Location: ../views/dashboardCocina.php');
            break;
        case 'representante':
            header('Location: ../views/dashboardRepresentante.php');
            break;
        default:
            header('Location: ../views/login.php?error=roleNotFound');
            break;
    }
    exit();
}