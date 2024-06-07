<?php
include 'db.php'; // Asegúrate de que este path sea correcto

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Cargar usuarios
        $query = "SELECT id, username, email, role FROM usuarios";
        $stmt = $pdo->query($query);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
        break;

    case 'POST':
        // Crear o actualizar usuarios
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null; // Encriptar contraseña
        $role = $_POST['role'];
        $userId = $_POST['userId'];

        if ($userId) {
            $query = "UPDATE usuarios SET username = ?, email = ?, role = ? WHERE id = ?";
            $params = [$username, $email, $role, $userId];
            if ($password) { // Solo actualiza la contraseña si se proporciona una
                $query = "UPDATE usuarios SET username = ?, email = ?, password = ?, role = ? WHERE id = ?";
                $params = [$username, $email, $password, $role, $userId];
            }
        } else {
            $query = "INSERT INTO usuarios (username, email, password, role) VALUES (?, ?, ?, ?)";
            $params = [$username, $email, $password, $role];
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Eliminar un usuario
        parse_str(file_get_contents("php://input"), $_DELETE);
        $userId = $_DELETE['userId'];
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId]);
        echo json_encode(['success' => true]);
        break;

    default:
        // Método no soportado
        header("HTTP/1.1 405 Method Not Allowed");
        exit();
}

