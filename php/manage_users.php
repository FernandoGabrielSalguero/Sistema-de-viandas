<?php
include 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $query = "SELECT id, username, email, role, saldo FROM usuarios";
        $stmt = $pdo->query($query);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
        break;

    case 'POST':
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        $role = $_POST['role'];
        $saldo = ($_POST['role'] == 'colegio') ? $_POST['saldo'] : 0; // Solo seteamos saldo si el rol es colegio
        $userId = $_POST['userId'] ?? '';

        if ($userId) {
            $query = "UPDATE usuarios SET username = ?, email = ?, role = ?, saldo = ?" . ($password ? ", password = ?" : "") . " WHERE id = ?";
            $params = [$username, $email, $role, $saldo];
            if ($password) $params[] = $password;
            $params[] = $userId;
        } else {
            $query = "INSERT INTO usuarios (username, email, password, role, saldo) VALUES (?, ?, ?, ?, ?)";
            $params = [$username, $email, $password, $role, $saldo];
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        $userId = json_decode(file_get_contents("php://input"), true)['userId'];
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId]);
        echo json_encode(['success' => $stmt->rowCount() > 0]);
        break;

    default:
        header("HTTP/1.1 405 Method Not Allowed");
        exit();
}
