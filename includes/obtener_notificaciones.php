<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cocina') {
    http_response_code(403); // Forbidden
    exit();
}

// Consultar las notificaciones pendientes
$consulta_notificaciones = $pdo->prepare("
    SELECT n.id, n.tipo, n.descripcion, u.Nombre 
    FROM notificaciones_cocina n 
    JOIN Usuarios u ON n.usuario_id = u.Id 
    WHERE n.estado = 'pendiente'
");
$consulta_notificaciones->execute();
$notificaciones = $consulta_notificaciones->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($notificaciones);
