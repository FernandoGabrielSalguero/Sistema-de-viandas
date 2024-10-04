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
    http_response_code(403); // Acceso prohibido
    exit();
}

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);
$notificacion_id = $data['id'] ?? null;

if ($notificacion_id) {
    // Actualizar el estado de la notificación a 'vista'
    $stmt = $pdo->prepare("UPDATE notificaciones_cocina SET estado = 'visto', visto_por = ? WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id'], $notificacion_id]);
    
    if ($stmt->rowCount() > 0) {
        // Responder con éxito si se actualizó correctamente
        echo json_encode(['success' => true]);
    } else {
        // Responder con error si no se actualizó
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo marcar como vista.']);
    }
} else {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'ID de notificación no proporcionado.']);
}
