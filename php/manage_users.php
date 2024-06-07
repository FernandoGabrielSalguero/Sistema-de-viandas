<?php include '../headers/header_admin.php'; ?>
<?php
include 'db.php'; // Asegúrate de que este path sea correcto

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $query = "SELECT id, username, email, role FROM usuarios";
        $stmt = $pdo->query($query);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
        break;

    case 'POST':
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        $role = $_POST['role'];
        $userId = $_POST['userId'] ?? '';

        if ($userId) {
            $query = $password ? "UPDATE usuarios SET username = ?, email = ?, password = ?, role = ? WHERE id = ?" :
                                 "UPDATE usuarios SET username = ?, email = ?, role = ? WHERE id = ?";
            $params = $password ? [$username, $email, $password, $role, $userId] : [$username, $email, $role, $userId];
        } else {
            $query = "INSERT INTO usuarios (username, email, password, role) VALUES (?, ?, ?, ?)";
            $params = [$username, $email, $password, $role];
        }
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
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
