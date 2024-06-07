<?php
session_start();
require_once 'common/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $userDetails = authenticateUser($username, $password);
    if ($userDetails) {
        $_SESSION['user_id'] = $userDetails['user_id'];
        $_SESSION['role'] = $userDetails['role'];
        header('Location: dashboard/' . $userDetails['role'] . '.php');
        exit();
    } else {
        $error_message = "Invalid username or password";
    }
}

function authenticateUser($username, $password) {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT user_id, role, password FROM Users WHERE username = :username");
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        if (password_verify($password, $user['password'])) {
            return ['user_id' => $user['user_id'], 'role' => $user['role']];
        }
    }
    return false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
    <form method="post">
        Username: <input type="text" name="username"><br>
        Password: <input type="password" name="password"><br>
        <input type="submit" value="Login">
    </form>
    <?php if (!empty($error_message)) { echo $error_message; } ?>
</body>
</html>
