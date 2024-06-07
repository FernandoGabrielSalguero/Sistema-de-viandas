<?php
include 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

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
        $colegio_id = $_DELETE['colegio_id'];
        $query = "DELETE FROM colegios WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$colegio_id]);
        echo json_encode(['success' => true]);
        break;

    case 'POST':
        if (isset($_POST['curso_nombre']) && isset($_POST['colegio_id'])) {
            $curso_nombre = $_POST['curso_nombre'];
            $colegio_id = $_POST['colegio_id'];
            $query = "INSERT INTO cursos (colegio_id, nombre) VALUES (?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$colegio_id, $curso_nombre]);
            echo json_encode(['success' => true]);
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

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);
        if (isset($_DELETE['curso_id'])) {
            $curso_id = $_DELETE['curso_id'];
            $query = "DELETE FROM cursos WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$curso_id]);
            echo json_encode(['success' => true]);
        }
        break;

    default:
        header("HTTP/1.1 405 Method Not Allowed");
        exit();
}
