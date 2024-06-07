<?php
include 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['action']) && $_GET['action'] === 'get_cursos' && isset($_GET['colegio_id'])) {
                $query = "SELECT id, nombre FROM cursos WHERE colegio_id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$_GET['colegio_id']]);
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
            $nombre = $_POST['nombre'];
            if (isset($_POST['colegio_id'])) {
                $colegio_id = $_POST['colegio_id'];
                $query = "UPDATE colegios SET nombre = ? WHERE id = ?";
                $params = [$nombre, $colegio_id];
            } else {
                $query = "INSERT INTO colegios (nombre) VALUES (?)";
                $params = [$nombre];
            }
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            echo json_encode(['success' => true]);
            break;

        case 'DELETE':
            parse_str(file_get_contents("php://input"), $_DELETE);
            $colegio_id = $_DELETE['colegio_id'] ?? null;
            if ($colegio_id) {
                $query = "DELETE FROM colegios WHERE id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$colegio_id]);
                echo json_encode(['success' => true]);
            } else {
                $curso_id = $_DELETE['curso_id'] ?? null;
                if ($curso_id) {
                    $query = "DELETE FROM cursos WHERE id = ?";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$curso_id]);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No ID provided for deletion']);
                }
            }
            break;

        case 'PUT':
            parse_str(file_get_contents("php://input"), $_PUT);
            if (isset($_PUT['curso_id']) && isset($_PUT['curso_nombre'])) {
                $curso_id = $_PUT['curso_id'];
                $curso_nombre = $_PUT['curso_nombre'];
                $query = "UPDATE cursos SET nombre = ? WHERE id = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$curso_nombre, $curso_id]);
                echo json_encode(['success' => true]);
            }
            break;

        default:
            header("HTTP/1.1 405 Method Not Allowed");
            exit();
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
