<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $query = "SELECT * FROM usuarios WHERE username = :username AND password = :password";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['username' => $username, 'password' => $password]);

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirigir al usuario a su dashboard correspondiente
        switch ($user['role']) {
            case 'admin':
                header("Location: /dashboard/admin.php");
                break;
            case 'colegio':
                header("Location: /dashboard/colegios.php");
                break;
            // Añadir más casos para otros roles
        }
        exit();
    } else {
        // Redirigir de vuelta al login con un mensaje de error
        header("Location: index.php?error=invalid_credentials");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login | Viandas</title>
    <link rel="stylesheet" href="css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <form method="post">
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Ingresar</button>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid_credentials'): ?>
            <p style="color: red;">Usuario o contraseña incorrectos.</p>
        <?php endif; ?>
    </form>
</body>
</html>
