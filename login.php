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
                case 'cuyo_placa':
                    echo "<script>console.log('Redirigiendo a cuyo/dashboard_cuyo_placa.php');</script>";
                    header("Location: cuyo/dashboard_cuyo_placa.php");
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
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .error {
            color: red;
            margin-bottom: 20px;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            color: #666;
        }

        input[type="text"], input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #6200ea;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #3700b3;
        }

        .social-login {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .social-login a {
            display: block;
            width: 48%;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
            text-decoration: none;
            color: #fff;
            font-size: 14px;
        }

        .google-login {
            background-color: #db4437;
        }

        .facebook-login {
            background-color: #4267b2;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ccc;
        }

        .divider:not(:empty)::before {
            margin-right: .25em;
        }

        .divider:not(:empty)::after {
            margin-left: .25em;
        }

        .register-link {
            margin-top: 20px;
            font-size: 14px;
        }

        .register-link a {
            color: #6200ea;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
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
