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

// Detectar si se solicita contar las notificaciones o listar notificaciones pendientes
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'count') {
        // Contar el número de notificaciones pendientes
        $consulta_notificaciones = $pdo->prepare("SELECT COUNT(*) as pendientes FROM notificaciones_cocina WHERE estado = 'pendiente'");
        $consulta_notificaciones->execute();
        $notificaciones = $consulta_notificaciones->fetch(PDO::FETCH_ASSOC);
        echo $notificaciones['pendientes'];

    } elseif ($action === 'list') {
        // Listar las notificaciones pendientes con sus detalles
        $consulta = $pdo->prepare("SELECT n.id, n.tipo, n.descripcion, u.Nombre FROM notificaciones_cocina n 
                                    JOIN usuarios u ON n.usuario_id = u.Id 
                                    WHERE n.estado = 'pendiente'");
        $consulta->execute();
        $notificaciones = $consulta->fetchAll(PDO::FETCH_ASSOC);

        if (count($notificaciones) > 0) {
            foreach ($notificaciones as $notificacion) {
                echo '<div class="dropdown-item">';
                echo '<p><strong>Tipo:</strong> ' . htmlspecialchars($notificacion['tipo']) . '</p>';
                echo '<p><strong>Nombre:</strong> ' . htmlspecialchars($notificacion['Nombre']) . '</p>';
                echo '<p><strong>Descripción:</strong> ' . htmlspecialchars($notificacion['descripcion']) . '</p>';
                echo '<button class="visto-btn" onclick="marcarComoVisto(' . $notificacion['id'] . ')">Visto</button>';
                echo '</div>';
            }
        } else {
            echo '<div class="no-notificaciones">No hay cambios por el momento, próxima actualización en 5 minutos</div>';
        }

    } elseif ($action === 'mark_seen' && isset($_POST['id'])) {
        // Marcar notificación como "vista"
        $notificacion_id = (int) $_POST['id'];
        $stmt = $pdo->prepare("UPDATE notificaciones_cocina SET estado = 'vista' WHERE id = ?");
        $stmt->execute([$notificacion_id]);

        // Devolver el número actualizado de notificaciones pendientes
        $consulta_notificaciones = $pdo->prepare("SELECT COUNT(*) as pendientes FROM notificaciones_cocina WHERE estado = 'pendiente'");
        $consulta_notificaciones->execute();
        $notificaciones = $consulta_notificaciones->fetch(PDO::FETCH_ASSOC);
        echo $notificaciones['pendientes'];
    }
}
