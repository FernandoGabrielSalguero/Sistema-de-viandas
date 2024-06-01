<?php
include 'header.php';
?>

<div class="container">
    <h3>Crear Nuevo Usuario</h3>
    <form action="../php/add_user.php" method="POST">
        <div class="input-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <div class="input-group">
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" required>
        </div>
        <div class="input-group">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required>
        </div>
        <div class="input-group">
            <label for="contraseña">Contraseña:</label>
            <input type="password" id="contraseña" name="contraseña" required>
        </div>
        <div class="input-group">
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono">
        </div>
        <div class="input-group">
            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo" required>
        </div>
        <div class="input-group">
            <label for="rol">Rol:</label>
            <select id="rol" name="rol" required>
                <option value="Administrador">Administrador</option>
                <option value="Usuario">Usuario</option>
            </select>
        </div>

        <div id="hijos-container" style="display: none;">
            <h4>Hijos</h4>
            <div id="hijos-forms">
                <!-- Los formularios de los hijos se agregarán aquí -->
            </div>
            <button type="button" id="add-hijo-button">Agregar Hijo</button>
        </div>

        <button type="submit">Crear Usuario</button>
    </form>

    <h3>Listar Usuarios</h3>
    <table class="material-design-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Usuario</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Saldo</th>
                <th>Notas de los Hijos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include '../php/db.php';
            $sql = "SELECT * FROM usuarios";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['nombre'] . "</td>";
                    echo "<td>" . $row['apellido'] . "</td>";
                    echo "<td>" . $row['usuario'] . "</td>";
                    echo "<td>" . $row['telefono'] . "</td>";
                    echo "<td>" . $row['correo'] . "</td>";
                    echo "<td>" . $row['rol'] . "</td>";
                    echo "<td>
                            <form action='../php/update_saldo.php' method='POST'>
                                <input type='hidden' name='id' value='" . $row['id'] . "'>
                                <input type='number' step='0.01' name='saldo' value='" . $row['saldo'] . "'>
                                <button type='submit'>Actualizar</button>
                            </form>
                          </td>";
                    echo "<td>";
                    // Obtener las notas de los hijos
                    $sqlHijos = "SELECT * FROM hijos WHERE usuario_id = " . $row['id'];
                    $resultHijos = $conn->query($sqlHijos);
                    if ($resultHijos->num_rows > 0) {
                        while($hijo = $resultHijos->fetch_assoc()) {
                            echo "<p>" . $hijo['nombre'] . " " . $hijo['apellido'] . " (Curso: " . $hijo['curso'] . "): " . $hijo['notas'] . "</p>";
                        }
                    } else {
                        echo "<p>Sin notas</p>";
                    }
                    echo "</td>";
                    echo "<td>
                            <a href='../php/edit_user.php?id=" . $row['id'] . "'>Editar</a> |
                            <a href='../php/delete_user.php?id=" . $row['id'] . "' class='delete-button'>Eliminar</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No hay usuarios disponibles</td></tr>";
            }
            ?>
        </tbody>
    </table>
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
