<?php
include 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['action']) && $_GET['action'] === 'get_cursos') {
                $colegio_id = $_GET['colegio_id'] ?? null;
                $query = $colegio_id ? "SELECT id, nombre FROM cursos WHERE colegio_id = ?" : "SELECT c.id, c.nombre, col.nombre AS colegio_nombre FROM cursos c JOIN colegios col ON c.colegio_id = col.id";
                $stmt = $pdo->prepare($query);
                $stmt->execute($colegio_id ? [$colegio_id] : []);
                $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($cursos);
            } else {
                $query = "SELECT id, nombre FROM colegios";
                $stmt = $pdo->query($query);
                $colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($colegios);
            }
            break;

        case 'POST':
            $nombre = $_POST['nombre'] ?? null;
            error_log('Received POST request with data: ' . json_encode($_POST)); // Log received data
            if (!$nombre) {
                throw new Exception('El nombre es requerido');
            }
            if (isset($_POST['colegio_id']) && !empty($_POST['colegio_id'])) {
                $colegio_id = $_POST['colegio_id'];
                $query = "UPDATE colegios SET nombre = ? WHERE id = ?";
                $params = [$nombre, $colegio_id];
                error_log('Executing query: ' . $query . ' with params: ' . json_encode($params)); // Log query and params
            } else if (isset($_POST['curso_nombre']) && isset($_POST['colegio_id'])) {
                $curso_nombre = $_POST['curso_nombre'];
                $colegio_id = $_POST['colegio_id'];
                $query = "INSERT INTO cursos (colegio_id, nombre) VALUES (?, ?)";
                $params = [$colegio_id, $curso_nombre];
                error_log('Executing query: ' . $query . ' with params: ' . json_encode($params)); // Log query and params
            } else {
                $query = "INSERT INTO colegios (nombre) VALUES (?)";
                $params = [$nombre];
                error_log('Executing query: ' . $query . ' with params: ' . json_encode($params)); // Log query and params
            }
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Error al guardar el colegio o curso');
            }
            break;

        case 'DELETE':
            parse_str(file_get_contents("php://input"), $_DELETE);
            error_log('Received DELETE request with data: ' . json_encode($_DELETE));
            $colegio_id = $_DELETE['colegio_id'] ?? null;
            $curso_id = $_DELETE['curso_id'] ?? null;
            if ($colegio_id) {
                $query = "DELETE FROM colegios WHERE id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$colegio_id]);
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Error al eliminar el colegio');
                }
            } else if ($curso_id) {
                $query = "DELETE FROM cursos WHERE id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$curso_id]);
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Error al eliminar el curso');
                }
            } else {
                throw new Exception('No ID provided for deletion');
            }
            break;

        case 'PUT':
            parse_str(file_get_contents("php://input"), $_PUT);
            error_log('Received PUT request with data: ' . json_encode($_PUT));
            if (isset($_PUT['curso_id']) && isset($_PUT['curso_nombre'])) {
                $curso_id = $_PUT['curso_id'];
                $curso_nombre = $_PUT['curso_nombre'];
                $query = "UPDATE cursos SET nombre = ? WHERE id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$curso_nombre, $curso_id]);
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Error al actualizar el curso');
                }
            } else {
                throw new Exception('Curso ID y nombre son requeridos');
            }
            break;

        default:
            header("HTTP/1.1 405 Method Not Allowed");
            exit();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
