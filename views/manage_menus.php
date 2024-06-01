<?php
include 'header.php';
?>

<div class="container">
    <h3>Crear Menú de Viandas Semanales</h3>
    <form action="../php/add_menu.php" method="POST">
        <div class="input-group">
            <label for="nombre">Nombre de la Vianda:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        <div class="input-group">
            <label for="precio">Precio:</label>
            <input type="number" step="0.01" id="precio" name="precio" required>
        </div>
        <div class="input-group">
            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" required>
        </div>
        <button type="submit">Crear Menú</button>
    </form>

    <h3>Listar Menús Semanales</h3>
    <table class="material-design-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include '../php/db.php';
            $sql = "SELECT * FROM menus";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['nombre'] . "</td>";
                    echo "<td>" . $row['precio'] . "</td>";
                    echo "<td>" . $row['fecha'] . "</td>";
                    echo "<td>
                            <a href='../php/edit_menu.php?id=" . $row['id'] . "'>Editar</a> |
                            <a href='../php/delete_menu.php?id=" . $row['id'] . "' class='delete-button'>Eliminar</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay menús disponibles</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>