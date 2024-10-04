<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cocina') {
    http_response_code(403); // Acceso prohibido
    exit();
}

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);
$notificacion_id = $data['id'] ?? null;

if ($notificacion_id) {
    // Actualizar el estado de la notificación a 'visto'
    $stmt = $pdo->prepare("UPDATE notificaciones_cocina SET estado = 'visto' WHERE id = ?");
    $stmt->execute([$notificacion_id]);
    
    if ($stmt->rowCount() > 0) {
        // Si la actualización fue exitosa
        echo json_encode(['success' => true]);
    } else {
        // Si no se pudo actualizar
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo marcar como vista.']);
    }
} else {
    // Si no se proporcionó un ID de notificación
    http_response_code(400);
    echo json_encode(['error' => 'ID de notificación no proporcionado.']);
}
