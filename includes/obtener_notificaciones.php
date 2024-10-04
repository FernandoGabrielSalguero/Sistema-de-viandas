<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';
include 'functions.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cocina') {
    http_response_code(403);
    exit("Acceso denegado");
}

try {

    $consulta_notificaciones = $pdo->prepare("
        SELECT nc.id, nc.tipo, nc.descripcion, u.Nombre 
        FROM notificaciones_cocina nc
        JOIN Usuarios u ON nc.usuario_id = u.Id
        WHERE nc.estado = 'pendiente'
    ");
    $consulta_notificaciones->execute();

    $notificaciones = $consulta_notificaciones->fetchAll(PDO::FETCH_ASSOC);

    if ($notificaciones === false) {
        throw new Exception("Error al obtener las notificaciones");
    }

    echo json_encode($notificaciones);
} catch (Exception $e) {

    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
