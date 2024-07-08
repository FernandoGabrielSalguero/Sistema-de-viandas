<?php
session_start();
require_once '../config/database.php';

// Capturar la acción del formulario
$action = $_POST['action'] ?? '';

if ($action == 'login') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = authenticateUser($username, $password);
    if ($user) {
        // Configurar variables de sesión
        $_SESSION['user_id'] = $user['Id'];
        $_SESSION['username'] = $user['Usuario'];
        $_SESSION['role'] = $user['Rol'];

        // Redirigir según el rol
        if ($user['Rol'] == 'administrador') {
            header('Location: ../views/dashboardAdmin.php');
        } elseif ($user['Rol'] == 'papas') {
            header('Location: ../views/dashboardPapas.php');
        } elseif ($user['Rol'] == 'cocina') {
            header('Location: ../views/dashboardCocina.php');
        } elseif ($user['Rol'] == 'representante') {
            header('Location: ../views/dashboardRepresentante.php');
        }
        exit();
    } else {
        // Autenticación fallida, redirigir a la página de login con un mensaje de error
        header('Location: ../views/login.php?error=loginFailed');
        exit();
    }
}
// Función de autenticación como la definida anteriormente
function authenticateUser($username, $password) {
    $pdo = getDB();
    $sql = "SELECT * FROM Usuarios WHERE Usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Contrasena'])) {
        return $user; // Autenticación exitosa
    }
    return false; // Falla la autenticación
}
