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
            $usuario_id = mysqli_insert_id($conn);

            // Insertar los hijos del usuario en la tabla de hijos
            if (isset($_POST['hijo_nombre']) && isset($_POST['hijo_curso'])) {
                $hijo_nombres = $_POST['hijo_nombre'];
                $hijo_cursos = $_POST['hijo_curso'];

                for ($i = 0; $i < count($hijo_nombres); $i++) {
                    $hijo_nombre = $hijo_nombres[$i];
                    $hijo_curso = $hijo_cursos[$i];

                    if (!empty($hijo_nombre) && !empty($hijo_curso)) {
                        $query_hijo = "INSERT INTO hijos (usuario_id, nombre, curso) VALUES ('$usuario_id', '$hijo_nombre', '$hijo_curso')";
                        mysqli_query($conn, $query_hijo);
                    }
                }
            }

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

            <!-- Campos para agregar hijos -->
            <div id="hijos-container">
                <h3>Hijos</h3>
                <div class="hijo-form">
                    <label for="hijo_nombre_1">Nombre del Hijo</label>
                    <input type="text" id="hijo_nombre_1" name="hijo_nombre[]">
                    <label for="hijo_curso_1">Curso</label>
                    <input type="text" id="hijo_curso_1" name="hijo_curso[]">
                </div>
            </div>
            <button type="button" id="agregar-hijo">Agregar otro hijo</button>

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

    <script>
        document.getElementById('agregar-hijo').addEventListener('click', function() {
            const hijosContainer = document.getElementById('hijos-container');
            const hijoCount = hijosContainer.getElementsByClassName('hijo-form').length + 1;
            const newHijoForm = document.createElement('div');
            newHijoForm.classList.add('hijo-form');
            newHijoForm.innerHTML = `
                <label for="hijo_nombre_${hijoCount}">Nombre del Hijo</label>
                <input type="text" id="hijo_nombre_${hijoCount}" name="hijo_nombre[]">
                <label for="hijo_curso_${hijoCount}">Curso</label>
                <input type="text" id="hijo_curso_${hijoCount}" name="hijo_curso[]">
            `;
            hijosContainer.appendChild(newHijoForm);
        });
    </script>
</body>
</html>
