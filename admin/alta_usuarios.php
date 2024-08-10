<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';
include '../includes/load_env.php';

// Cargar variables del archivo .env
loadEnv(__DIR__ . '/../.env');

// Función para enviar correo electrónico usando SMTP
function enviarCorreo($to, $subject, $message) {
    $headers = "From: " . getenv('SMTP_USERNAME') . "\r\n" .
               "Reply-To: " . getenv('SMTP_USERNAME') . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Configuración del transporte SMTP
    $params = [
        'host' => getenv('SMTP_HOST'),
        'port' => getenv('SMTP_PORT'),
        'auth' => true,
        'username' => getenv('SMTP_USERNAME'),
        'password' => getenv('SMTP_PASSWORD'),
    ];

    // Usar la función mail() de PHP
    ini_set('SMTP', $params['host']);
    ini_set('smtp_port', $params['port']);
    ini_set('sendmail_from', $params['username']);

    return mail($to, $subject, $message, $headers);
}

// Procesar el formulario cuando se envíe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_usuario'])) {
    $nombre = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena']; // Guardar la contraseña sin cifrar para el correo
    $contrasena_cifrada = password_hash($contrasena, PASSWORD_BCRYPT);
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];
    $saldo = $_POST['saldo'];

    // Validar que todos los campos estén llenos
    if (empty($nombre) || empty($usuario) || empty($contrasena) || empty($telefono) || empty($correo) || empty($rol) || empty($saldo)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Insertar el nuevo usuario en la base de datos
        $stmt = $pdo->prepare("INSERT INTO Usuarios (Nombre, Usuario, Contrasena, Telefono, Correo, Rol, Saldo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$nombre, $usuario, $contrasena_cifrada, $telefono, $correo, $rol, $saldo])) {
            $success = "Usuario creado con éxito.";

            // Enviar correo electrónico
            $subject = "Bienvenido a Ilmana Gastronomía";
            $message = "Estimado $nombre,\n\nSu cuenta ha sido creada exitosamente. Link nueva plataforma: https://viandas.ilmanagastronomia.com/ \n\nUsuario: $usuario\nContraseña: $contrasena\n\nSaludos,\nEquipo de Ilmana Gastronomía";

            if (enviarCorreo($correo, $subject, $message)) {
                $success .= " Se ha enviado un correo electrónico al usuario.";
            } else {
                $error = "No se pudo enviar el correo electrónico.";
            }
        } else {
            $error = "Hubo un error al crear el usuario.";
        }
    }
}

// Procesar la eliminación de un usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_usuario'])) {
    $usuario_id = $_POST['usuario_id'];
    try {
        // Eliminar las referencias en la tabla Usuarios_Hijos
        $stmt = $pdo->prepare("DELETE FROM Usuarios_Hijos WHERE Usuario_Id = ?");
        $stmt->execute([$usuario_id]);
        
        // Ahora eliminar el usuario de la tabla Usuarios
        $stmt = $pdo->prepare("DELETE FROM Usuarios WHERE Id = ?");
        $stmt->execute([$usuario_id]);
        
        $success = "Usuario eliminado con éxito.";
    } catch (PDOException $e) {
        $error = "Hubo un error al eliminar el usuario: " . $e->getMessage();
    }
}

// Procesar la actualización de un usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_usuario'])) {
    $usuario_id = $_POST['usuario_id'];
    $nombre = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];
    $saldo = $_POST['saldo'];

    // Actualizar el usuario en la base de datos
    $stmt = $pdo->prepare("UPDATE Usuarios SET Nombre = ?, Usuario = ?, Telefono = ?, Correo = ?, Rol = ?, Saldo = ? WHERE Id = ?");
    if ($stmt->execute([$nombre, $usuario, $telefono, $correo, $rol, $saldo, $usuario_id])) {
        $success = "Usuario actualizado con éxito.";
    } else {
        $error = "Hubo un error al actualizar el usuario.";
    }
}

// Obtener todos los usuarios con filtros
$nombre_filtro = isset($_GET['nombre_filtro']) ? $_GET['nombre_filtro'] : '';
$usuario_filtro = isset($_GET['usuario_filtro']) ? $_GET['usuario_filtro'] : '';
$correo_filtro = isset($_GET['correo_filtro']) ? $_GET['correo_filtro'] : '';

$query = "SELECT * FROM Usuarios WHERE 1=1";
$params = [];

if (!empty($nombre_filtro)) {
    $query .= " AND Nombre LIKE ?";
    $params[] = '%' . $nombre_filtro . '%';
}
if (!empty($usuario_filtro)) {
    $query .= " AND Usuario LIKE ?";
    $params[] = '%' . $usuario_filtro . '%';
}
if (!empty($correo_filtro)) {
    $query .= " AND Correo LIKE ?";
    $params[] = '%' . $correo_filtro . '%';
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de usuarios</title>
    <link rel="stylesheet" href="../css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>Gestión de usuarios</h1>
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
            <option value="cuyo_placa">Cuyo Placa</option>
            <option value="transporte_ld">Transporte Larga Distancia</option> <!-- Agregados nuevos roles -->
        </select>

        <label for="saldo">Saldo</label>
        <input type="number" step="0.01" id="saldo" name="saldo" required>
        
        <button type="submit" name="crear_usuario">Crear Usuario</button>
    </form>

    <h2>Filtros</h2>
    <form method="get" action="alta_usuarios.php">
        <label for="nombre_filtro">Nombre</label>
        <input type="text" id="nombre_filtro" name="nombre_filtro" value="<?php echo htmlspecialchars($nombre_filtro); ?>">
        
        <label for="usuario_filtro">Usuario</label>
        <input type="text" id="usuario_filtro" name="usuario_filtro" value="<?php echo htmlspecialchars($usuario_filtro); ?>">
        
        <label for="correo_filtro">Correo</label>
        <input type="text" id="correo_filtro" name="correo_filtro" value="<?php echo htmlspecialchars($correo_filtro); ?>">

        <button type="submit">Buscar</button>
    </form>

    <h2>Usuarios Registrados</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Saldo</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($usuarios as $usuario) : ?>
        <tr>
            <form method="post" action="alta_usuarios.php">
                <td><?php echo htmlspecialchars($usuario['Id']); ?></td>
                <td><input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['Nombre']); ?>" required></td>
                <td><input type="text" name="usuario" value="<?php echo htmlspecialchars($usuario['Usuario']); ?>" required></td>
                <td><input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario['Telefono']); ?>" required></td>
                <td><input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['Correo']); ?>" required></td>
                <td>
                    <select name="rol" required>
                        <option value="papas" <?php echo ($usuario['Rol'] == 'papas') ? 'selected' : ''; ?>>Papás</option>
                        <option value="cocina" <?php echo ($usuario['Rol'] == 'cocina') ? 'selected' : ''; ?>>Cocina</option>
                        <option value="representante" <?php echo ($usuario['Rol'] == 'representante') ? 'selected' : ''; ?>>Representante</option>
                        <option value="administrador" <?php echo ($usuario['Rol'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                        <option value="cuyo_placa" <?php echo ($usuario['Rol'] == 'cuyo_placa') ? 'selected' : ''; ?>>Cuyo Placa</option>
                        <option value="transporte_ld" <?php echo ($usuario['Rol'] == 'transporte_ld') ? 'selected' : ''; ?>>Transporte Larga Distancia</option> <!-- Agregados nuevos roles -->
                    </select>
                </td>
                <td><input type="number" step="0.01" name="saldo" value="<?php echo htmlspecialchars($usuario['Saldo']); ?>" required></td>
                <td>
                    <input type="hidden" name="usuario_id" value="<?php echo htmlspecialchars($usuario['Id']); ?>">
                    <button type="submit" name="actualizar_usuario">Actualizar</button>
                    <button type="submit" name="eliminar_usuario" onclick="return confirm('¿Está seguro de que desea eliminar este usuario?');">Eliminar</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
