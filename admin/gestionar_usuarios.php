<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../index.php');
    exit;
}

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lógica para agregar un nuevo usuario
    if (isset($_POST['crear_usuario'])) {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $usuario = $_POST['usuario'];
        $contrasena = $_POST['contrasena'];
        $telefono = $_POST['telefono'];
        $correo = $_POST['correo'];
        $rol = $_POST['rol'];

        $query = "INSERT INTO usuarios (nombre, apellido, usuario, contrasena, telefono, correo, rol) VALUES ('$nombre', '$apellido', '$usuario', '$contrasena', '$telefono', '$correo', '$rol')";
        if (mysqli_query($conn, $query)) {
            echo "Usuario creado con éxito.";
        } else {
            echo "Error: " . $query . "<br>" . mysqli_error($conn);
        }
    }

    // Lógica para modificar un usuario existente
    if (isset($_POST['modificar_usuario'])) {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $usuario = $_POST['usuario'];
        $contrasena = $_POST['contrasena'];
        $telefono = $_POST['telefono'];
        $correo = $_POST['correo'];
        $rol = $_POST['rol'];

        $query = "UPDATE usuarios SET nombre='$nombre', apellido='$apellido', usuario='$usuario', contrasena='$contrasena', telefono='$telefono', correo='$correo', rol='$rol' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo "Usuario modificado con éxito.";
        } else {
            echo "Error: " . $query . "<br>" . mysqli_error($conn);
        }
    }
}

// Obtener todos los usuarios
$query = "SELECT * FROM usuarios";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - Administrador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Gestionar Usuarios</h1>
        <h2>Crear Nuevo Usuario</h2>
        <form action="gestionar_usuarios.php" method="post">
            <input type="hidden" name="crear_usuario" value="1">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" required>
            </div>
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" required>
            </div>
            <div class="form-group">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            <div class="form-group">
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <option value="Usuario">Usuario</option>
                    <option value="Administrador">Administrador</option>
                </select>
            </div>
            <button type="submit">Crear Usuario</button>
        </form>

        <h2>Usuarios Existentes</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Usuario</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['nombre'] ?></td>
                        <td><?= $row['apellido'] ?></td>
                        <td><?= $row['usuario'] ?></td>
                        <td><?= $row['telefono'] ?></td>
                        <td><?= $row['correo'] ?></td>
                        <td><?= $row['rol'] ?></td>
                        <td>
                            <form action="gestionar_usuarios.php" method="post" style="display:inline-block;">
                                <input type="hidden" name="modificar_usuario" value="1">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="nombre" value="<?= $row['nombre'] ?>">
                                <input type="hidden" name="apellido" value="<?= $row['apellido'] ?>">
                                <input type="hidden" name="usuario" value="<?= $row['usuario'] ?>">
                                <input type="hidden" name="contrasena" value="<?= $row['contrasena'] ?>">
                                <input type="hidden" name="telefono" value="<?= $row['telefono'] ?>">
                                <input type="hidden" name="correo" value="<?= $row['correo'] ?>">
                                <input type="hidden" name="rol" value="<?= $row['rol'] ?>">
                                <button type="submit">Modificar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="dashboard.php">Volver al Dashboard</a>
    </div>
</body>
</html>