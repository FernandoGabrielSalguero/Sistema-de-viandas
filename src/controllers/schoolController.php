<?php
require_once '../config/database.php';

function addSchool($name, $address) {
    $pdo = getDB();
    $sql = "INSERT INTO Colegios (Nombre, Direccion) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $address]);

    return $pdo->lastInsertId(); // Devuelve el ID del colegio añadido
}

// Aquí podrías añadir más funciones para actualizar y listar colegios
