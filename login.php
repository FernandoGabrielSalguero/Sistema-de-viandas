<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];

    $stmt = $pdo->prepare("SELECT Id, Contraseña, Rol FROM Usuarios WHERE Usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($contraseña, $user['Contraseña'])) {
        $_SESSION['user_id'] = $user['Id'];
        $_SESSION['user_role'] = $user['Rol'];
        
        switch ($user['Rol']) {
            case 'administrador':
                header("Location: admin/dashboard.php");
                break;
            case 'cocina':
                header("Location: cocina/pedidos.php");
                break;
            case 'papas':
                header("Location: papas/cargar_saldo.php");
                break;
            case 'representante':
                header("Location: representante/pedidos.php");
                break;
        }
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <form method="post">
        <h2>Login</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required>
        <label for="contraseña">Contraseña</label>
        <input type="password" id="contraseña" name="contraseña" required>
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
