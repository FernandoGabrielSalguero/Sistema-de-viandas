<?php
include 'header.php';
include '../php/db.php';

// Obtener cursos
$sql = "SELECT * FROM cursos";
$result = $conn->query($sql);
$cursos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $cursos[] = $row;
    }
}
?>

<div class="container">
    <h3>Gestionar Cursos</h3>
    <form action="../php/add_curso.php" method="POST">
        <div class="input-group">
            <label for="nombre">Nombre del Curso:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <button type="submit">Añadir Curso</button>
    </form>

    <h3>Listar Cursos</h3>
    <table class="material-design-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (count($cursos) > 0) {
                foreach ($cursos as $curso) {
                    echo "<tr>";
                    echo "<td>" . $curso['id'] . "</td>";
                    echo "<td>" . $curso['nombre'] . "</td>";
                    echo "<td>
                            <a href='../php/edit_curso.php?id=" . $curso['id'] . "'>Editar</a> |
                            <a href='../php/delete_curso.php?id=" . $curso['id'] . "' class='delete-button'>Eliminar</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No hay cursos disponibles</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
