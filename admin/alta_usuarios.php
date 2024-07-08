<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Procesar el formulario cuando se envíe
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    // Validar que todos los campos estén llenos
    if (empty($nombre) || empty($usuario) || empty($contrasena) || empty($telefono) || empty($correo) || empty($rol)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Insertar el nuevo usuario en la base de datos
        $stmt = $pdo->prepare("INSERT INTO Usuarios (Nombre, Usuario, Contrasena, Telefono, Correo, Rol) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$nombre, $usuario, $contrasena, $telefono, $correo, $rol])) {
            $success = "Usuario creado con éxito.";
        } else {
            $error = "Hubo un error al crear el usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Usuarios</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Alta de Usuarios</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <form method="post" action="alta_usuarios.php">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" required>
        
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required>
        
        <label for="contrasena">Contraseña</label>
        <input type="password" id="contrasena" name="contrasena" required>
        
        <label for="telefono">Teléfono</label>
        <input type="text" id="telefono" name="telefono" required>
        
        <label for="correo">Correo</label>
        <input type="email" id="correo" name="correo" required>
        
        <label for="rol">Rol</label>
        <select id="rol" name="rol" required>
            <option value="papas">Papás</option>
            <option value="cocina">Cocina</option>
            <option value="representante">Representante</option>
            <option value="administrador">Administrador</option>
        </select>
        
        <button type="submit">Crear Usuario</button>
    </form>
</body>
</html>
