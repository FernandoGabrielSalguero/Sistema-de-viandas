<?php
include 'header.php';
include '../php/db.php';

// Obtener colegios
$sql = "SELECT * FROM colegios";
$result = $conn->query($sql);
$colegios = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $colegios[] = $row;
    }
}
?>

<div class="container">
    <h3>Gestionar Colegios</h3>
    <form action="../php/add_colegio.php" method="POST">
        <div class="input-group">
            <label for="nombre">Nombre del Colegio:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <button type="submit">Añadir Colegio</button>
    </form>

    <h3>Listar Colegios</h3>
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
            if (count($colegios) > 0) {
                foreach ($colegios as $colegio) {
                    echo "<tr>";
                    echo "<td>" . $colegio['id'] . "</td>";
                    echo "<td>" . $colegio['nombre'] . "</td>";
                    echo "<td>
                            <a href='../php/edit_colegio.php?id=" . $colegio['id'] . "'>Editar</a> |
                            <a href='../php/delete_colegio.php?id=" . $colegio['id'] . "' class='delete-button'>Eliminar</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No hay colegios disponibles</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
