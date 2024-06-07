<?php
session_start();
require 'db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM usuarios WHERE username = :username AND password = :password";
$stmt = $pdo->prepare($query);
$stmt->execute(['username' => $username, 'password' => $password]);

if ($stmt->rowCount() == 1) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    // Redirigir al usuario a su dashboard correspondiente
    switch ($user['role']) {
        case 'admin':
            header("Location: /dashboard/admin.php");
            break;
        case 'colegio':
            header("Location: /dashboard/colegios.php");
            break;
        // Añadir más casos para otros roles
    }
    exit();
} else {
    echo "Usuario o contraseña incorrectos.";
}
