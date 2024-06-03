<?php
include 'header.php';
?>

<div class="container">
    <h3>Listar Pedidos de Viandas</h3>
    <table class="material-design-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Hijo</th>
                <th>Menú</th>
                <th>Estado</th>
                <th>Fecha de Pedido</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            include '../php/db.php';
            $sql = "SELECT pedidos.id, usuarios.usuario, hijos.nombre AS hijo_nombre, hijos.apellido AS hijo_apellido, 
                           menus.nombre AS menu_nombre, pedidos.estado, pedidos.fecha_pedido
                    FROM pedidos
                    JOIN usuarios ON pedidos.usuario_id = usuarios.id
                    JOIN hijos ON pedidos.hijo_id = hijos.id
                    JOIN menus ON pedidos.menu_id = menus.id";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['usuario'] . "</td>";
                    echo "<td>" . $row['hijo_nombre'] . " " . $row['hijo_apellido'] . "</td>";
                    echo "<td>" . $row['menu_nombre'] . "</td>";
                    echo "<td>" . $row['estado'] . "</td>";
                    echo "<td>" . $row['fecha_pedido'] . "</td>";
                    echo "<td>
                            <a href='../php/edit_order.php?id=" . $row['id'] . "'>Editar</a> |
                            <a href='../php/delete_order.php?id=" . $row['id'] . "' class='delete-button'>Eliminar</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No hay pedidos disponibles</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
