<?php
include 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'get_children' && isset($_GET['userId'])) {
            $query = "SELECT id, nombre AS name, escuela, curso FROM hijos WHERE usuario_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$_GET['userId']]);
            $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($children);
        } else {
            $query = "SELECT id, username, email, role, saldo FROM usuarios";
            $stmt = $pdo->query($query);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
        }
        break;

    case 'POST':
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        $role = $_POST['role'];
        $saldo = ($_POST['role'] == 'colegio') ? $_POST['saldo'] : 0;
        $userId = $_POST['userId'] ?? '';

        if ($userId) {
            $query = $password ? "UPDATE usuarios SET username = ?, email = ?, password = ?, role = ?, saldo = ? WHERE id = ?" :
                                 "UPDATE usuarios SET username = ?, email = ?, role = ?, saldo = ? WHERE id = ?";
            $params = $password ? [$username, $email, $password, $role, $saldo, $userId] : [$username, $email, $role, $saldo, $userId];
        } else {
            $query = "INSERT INTO usuarios (username, email, password, role, saldo) VALUES (?, ?, ?, ?, ?)";
            $params = [$username, $email, $password, $role, $saldo];
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        if ($role === 'colegio') {
            $userId = $userId ? $userId : $pdo->lastInsertId();
            $stmt = $pdo->prepare("DELETE FROM hijos WHERE usuario_id = ?");
            $stmt->execute([$userId]);
            if (isset($_POST['childName'])) {
                $childNames = $_POST['childName'];
                $schools = $_POST['school'];
                $courses = $_POST['course'];
                for ($i = 0; $i < count($childNames); $i++) {
                    $stmt = $pdo->prepare("INSERT INTO hijos (usuario_id, nombre, escuela, curso) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$userId, $childNames[$i], $schools[$i], $courses[$i]]);
                }
            }
        }

        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);
        $userId = $_DELETE['userId'];
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId]);
        echo json_encode(['success' => true]);
        break;

    default:
        header("HTTP/1.1 405 Method Not Allowed");
        exit();
}
