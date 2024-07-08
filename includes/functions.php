<?php
// Funciones comunes

function getUserRole($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT Rol FROM Usuarios WHERE Id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

