<?php
include 'db.php';

if (!isset($_GET['id'])) {
    die("ID de usuario no especificado.");
}

$id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    // Actualizar usuario
    $sql = "UPDATE usuarios SET nombre='$nombre', apellido='$apellido', usuario='$usuario', contraseña='$contraseña', 
            telefono='$telefono', correo='$correo', rol='$rol' WHERE id=$id";
    $conn->query($sql);

    // Eliminar hijos existentes
    $sql = "DELETE FROM hijos WHERE usuario_id=$id";
    $conn->query($sql);

    // Insertar hijos si el rol es 'Usuario'
    if ($rol == 'Usuario' && isset($_POST['hijos'])) {
        foreach ($_POST['hijos'] as $hijo) {
            $hijo_nombre = $hijo['nombre'];
            $hijo_apellido = $hijo['apellido'];
            $hijo_curso = $hijo['curso'];
            $hijo_colegio = $hijo['colegio'];
            $hijo_notas = $hijo['notas'];

            $sql = "INSERT INTO hijos (nombre, apellido, curso, colegio, notas, usuario_id) 
                    VALUES ('$hijo_nombre', '$hijo_apellido', '$hijo_curso', '$hijo_colegio', '$hijo_notas', '$id')";
            $conn->query($sql);
        }
    }

    header("Location: ../views/manage_users.php");
    exit();
} else {
    // Obtener la información del usuario
    $sql = "SELECT * FROM usuarios WHERE id=$id";
    $result = $conn->query($sql);

    if ($result->num_rows != 1) {
        die("Usuario no encontrado.");
    }

    $usuario = $result->fetch_assoc();

    // Obtener la información de los hijos
    $sql = "SELECT * FROM hijos WHERE usuario_id=$id";
    $result = $conn->query($sql);
    $hijos = [];
    while ($row = $result->fetch_assoc()) {
        $hijos[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Editar Usuario - Viandas</title>
</head>
<body>
    <div class="header">
        <h1>Editar Usuario</h1>
        <a href="../php/logout.php">Logout</a>
    </div>
    <div class="container">
        <form action="edit_user.php?id=<?php echo $id; ?>" method="POST">
            <div class="input-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo $usuario['nombre']; ?>" required>
            </div>
            <div class="input-group">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" value="<?php echo $usuario['apellido']; ?>" required>
            </div>
            <div class="input-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" value="<?php echo $usuario['usuario']; ?>" required>
            </div>
            <div class="input-group">
                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" name="contraseña" value="<?php echo $usuario['contraseña']; ?>" required>
            </div>
            <div class="input-group">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" value="<?php echo $usuario['telefono']; ?>">
            </div>
            <div class="input-group">
                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo" value="<?php echo $usuario['correo']; ?>" required>
            </div>
            <div class="input-group">
                <label for="rol">Rol:</label>
                <select id="rol" name="rol" required>
                    <option value="Administrador" <?php echo $usuario['rol'] == 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                    <option value="Usuario" <?php echo $usuario['rol'] == 'Usuario' ? 'selected' : ''; ?>>Usuario</option>
                </select>
            </div>

            <div id="hijos-container" style="display: <?php echo $usuario['rol'] == 'Usuario' ? 'block' : 'none'; ?>;">
                <h4>Hijos</h4>
                <div id="hijos-forms">
                    <?php foreach ($hijos as $index => $hijo): ?>
                        <div class="input-group">
                            <h5>Hijo <?php echo $index + 1; ?></h5>
                            <label for="hijo_nombre_<?php echo $index; ?>">Nombre:</label>
                            <input type="text" id="hijo_nombre_<?php echo $index; ?>" name="hijos[<?php echo $index; ?>][nombre]" value="<?php echo $hijo['nombre']; ?>" required>
                            <label for="hijo_apellido_<?php echo $index; ?>">Apellido:</label>
                            <input type="text" id="hijo_apellido_<?php echo $index; ?>" name="hijos[<?php echo $index; ?>][apellido]" value="<?php echo $hijo['apellido']; ?>" required>
                            <label for="hijo_curso_<?php echo $index; ?>">Curso:</label>
                            <input type="text" id="hijo_curso_<?php echo $index; ?>" name="hijos[<?php echo $index; ?>][curso]" value="<?php echo $hijo['curso']; ?>" required>
                            <label for="hijo_colegio_<?php echo $index; ?>">Colegio:</label>
                            <input type="text" id="hijo_colegio_<?php echo $index; ?>" name="hijos[<?php echo $index; ?>][colegio]" value="<?php echo $hijo['colegio']; ?>" required>
                            <label for="hijo_notas_<?php echo $index; ?>">Notas:</label>
                            <textarea id="hijo_notas_<?php echo $index; ?>" name="hijos[<?php echo $index; ?>][notas]"><?php echo $hijo['notas']; ?></textarea>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-hijo-button">Agregar Hijo</button>
            </div>

            <button type="submit">Guardar Cambios</button>
        </form>
    </div>

    <script>
    document.getElementById('rol').addEventListener('change', function() {
        var hijosContainer = document.getElementById('hijos-container');
        if (this.value === 'Usuario') {
            hijosContainer.style.display = 'block';
        } else {
            hijosContainer.style.display = 'none';
        }
    });

    document.getElementById('add-hijo-button').addEventListener('click', function() {
        var hijosForms = document.getElementById('hijos-forms');
        var numHijos = hijosForms.children.length;

        if (numHijos < 10) {
            var nuevoHijoForm = document.createElement('div');
            nuevoHijoForm.classList.add('input-group');
            nuevoHijoForm.innerHTML = `
                <h5>Hijo ${numHijos + 1}</h5>
                <label for="hijo_nombre_${numHijos}">Nombre:</label>
                <input type="text" id="hijo_nombre_${numHijos}" name="hijos[${numHijos}][nombre]" required>
                <label for="hijo_apellido_${numHijos}">Apellido:</label>
                <input type="text" id="hijo_apellido_${numHijos}" name="hijos[${numHijos}][apellido]" required>
                <label for="hijo_curso_${numHijos}">Curso:</label>
                <input type="text" id="hijo_curso_${numHijos}" name="hijos[${numHijos}][curso]" required>
                <label for="hijo_colegio_${numHijos}">Colegio:</label>
                <input type="text" id="hijo_colegio_${numHijos}" name="hijos[${numHijos}][colegio]" required>
                <label for="hijo_notas_${numHijos}">Notas:</label>
                <textarea id="hijo_notas_${numHijos}" name="hijos[${numHijos}][notas]"></textarea>
            `;
            hijosForms.appendChild(nuevoHijoForm);
        } else {
            alert('El usuario no puede tener más de 10 hijos.');
        }
    });
    </script>

</body>
</html>
