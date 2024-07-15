<?php
session_start();
include 'includes/db.php';

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$rol = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $stmt = $pdo->prepare("SELECT Id, Contrasena, Rol FROM Usuarios WHERE Usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($contrasena, $user['Contrasena'])) {
        $_SESSION['usuario_id'] = $user['Id'];
        $_SESSION['rol'] = $user['Rol'];
        
        // Imprimir el rol del usuario y verificar que se establece correctamente
        echo "<script>console.log('Rol del usuario: " . $user['Rol'] . "');</script>";
        
        switch ($user['Rol']) {
            case 'administrador':
                echo "<script>console.log('Redirigiendo a admin/dashboard.php');</script>";
                header("Location: admin/dashboard.php");
                exit();
            case 'cocina':
                echo "<script>console.log('Redirigiendo a cocina/dashboard.php');</script>";
                header("Location: cocina/dashboard.php");
                exit();
            case 'papas':
                echo "<script>console.log('Redirigiendo a papas/dashboard.php');</script>";
                header("Location: papas/dashboard.php");
                exit();
            case 'representante':
                echo "<script>console.log('Redirigiendo a representante/dashboard.php');</script>";
                header("Location: representante/dashboard.php");
                exit();
            default:
                echo "<script>console.log('Rol no válido: " . $user['Rol'] . "');</script>";
                header("Location: login.php?error=Rol no válido");
                exit();
        }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
