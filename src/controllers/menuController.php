<?php
require_once '../config/database.php';

function addMenu($name, $deliveryDate, $price, $status) {
    $pdo = getDB();
    $sql = "INSERT INTO Menu (Nombre, Fecha_de_entrega, Precio, Estado) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $deliveryDate, $price, $status]);

    return $pdo->lastInsertId(); // Devuelve el ID del menú añadido
}

// Funciones adicionales para actualizar y listar menús podrían ser implementadas aquí
