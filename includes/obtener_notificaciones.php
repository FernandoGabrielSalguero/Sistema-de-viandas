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

// Consultar el número de notificaciones pendientes
$consulta_notificaciones = $pdo->prepare("SELECT COUNT(*) as pendientes FROM notificaciones_cocina WHERE estado = 'pendiente'");
$consulta_notificaciones->execute();
$notificaciones = $consulta_notificaciones->fetch(PDO::FETCH_ASSOC);
echo $notificaciones['pendientes'];
