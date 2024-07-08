<?php
require_once '../config/database.php';

function registerUser($name, $username, $password, $phone, $email, $role) {
    $pdo = getDB();
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO Usuarios (Nombre, Usuario, Contrasena, Telefono, Correo, Rol) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $username, $passwordHash, $phone, $email, $role]);

    return $pdo->lastInsertId();
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
