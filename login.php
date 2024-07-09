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
        
        // Almacenar el rol del usuario en una variable
        $rol = $user['Rol'];
        
        // Mensajes de depuración
        error_log("Usuario ID: " . $user['Id']);
        error_log("Rol: " . $user['Rol']);
        
        switch ($user['Rol']) {
            case 'administrador':
                error_log("Redirigiendo a admin/dashboard.php");
                if (file_exists('admin/dashboard.php')) {
                    error_log("Archivo admin/dashboard.php existe");
                } else {
                    error_log("Archivo admin/dashboard.php no existe");
                }
                header("Location: admin/dashboard.php");
                exit();
            case 'cocina':
                error_log("Redirigiendo a cocina/dashboard.php");
                header("Location: cocina/dashboard.php");
                exit();
            case 'papas':
                error_log("Redirigiendo a papas/dashboard.php");
                header("Location: papas/dashboard.php");
                exit();
            case 'representante':
                error_log("Redirigiendo a representante/dashboard.php");
                header("Location: representante/dashboard.php");
                exit();
            default:
                error_log("Rol no válido: " . $user['Rol']);
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
    <?php if ($rol): ?>
    <script>
        console.log("Rol del usuario: <?php echo $rol; ?>");
    </script>
    <?php endif; ?>
</body>
</html>
