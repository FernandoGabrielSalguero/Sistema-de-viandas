<?php
require_once '../config/database.php';

function addChild($name, $schoolId, $courseId, $preferences) {
    $pdo = getDB();
    $sql = "INSERT INTO Hijos (Nombre, Colegio, Curso, Preferencias_Alimenticias) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $schoolId, $courseId, $preferences]);
    return $pdo->lastInsertId();
}

// Funciones para actualizar, eliminar y listar hijos podrían ser implementadas aquí
