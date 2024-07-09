<?php
session_start();
include 'includes/db.php';

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $stmt = $pdo->prepare("SELECT Id, Contrasena, Rol FROM Usuarios WHERE Usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($contrasena, $user['Contrasena'])) {
        $_SESSION['usuario_id'] = $user['Id'];
        $_SESSION['rol'] = $user['Rol'];
        
        // Mensajes de depuración
        error_log("Usuario ID: " . $user['Id']);
        error_log("Rol: " . $user['Rol']);
        
        switch ($user['Rol']) {
            case 'administrador':
                error_log("Redirigiendo a admin/dashboard.php");
                header("Location: cocina/dashboard.php");
                break;
            case 'cocina':
                error_log("Redirigiendo a cocina/dashboard.php");
                header("Location: cocina/dashboard.php");
                break;
            case 'papas':
                error_log("Redirigiendo a papas/dashboard.php");
                header("Location: papas/dashboard.php");
                break;
            case 'representante':
                error_log("Redirigiendo a representante/dashboard.php");
                header("Location: representante/dashboard.php");
                break;
            default:
                error_log("Rol no válido: " . $user['Rol']);
                header("Location: login.php?error=Rol no válido");
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
    <title>¡Que gusto verde de nuevo!</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <form method="post">
        <h2>Ingresa tu usuario y contraseña</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required>
        <label for="contrasena">Contraseña</label>
        <input type="password" id="contrasena" name="contrasena" required>
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
