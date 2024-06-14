<?php
include '../common/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_menu'])) {
    $date = $_POST['date'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Insertar el nuevo menú en la base de datos
    $stmt = $pdo->prepare("INSERT INTO menus (date, name, description, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$date, $name, $description, $price]);

    echo "El menú ha sido creado exitosamente.";
}

// Obtener todos los menús existentes
$stmt = $pdo->prepare("SELECT * FROM menus ORDER BY date DESC");
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <form action="create_menu.php" method="post">
        <h2>Crear Menú de Viandas</h2>
        <label for="date">Fecha:</label>
        <input type="date" id="date" name="date" required>
        <label for="name">Nombre del Menú:</label>
        <input type="text" id="name" name="name" required>
        <label for="description">Descripción:</label>
        <textarea id="description" name="description"></textarea>
        <label for="price">Precio:</label>
        <input type="number" id="price" name="price" step="0.01" required>
        <button type="submit" name="create_menu">Crear Menú</button>
    </form>

    <h2>Menús Existentes</h2>
    <table id="menusTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">Fecha</th>
                <th onclick="sortTable(1)">Nombre</th>
                <th onclick="sortTable(2)">Descripción</th>
                <th onclick="sortTable(3)">Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($menus as $menu): ?>
            <tr>
                <td><?php echo htmlspecialchars($menu['date']); ?></td>
                <td><?php echo htmlspecialchars($menu['name']); ?></td>
                <td><?php echo htmlspecialchars($menu['description']); ?></td>
                <td><?php echo htmlspecialchars($menu['price']); ?></td>
                <td>
                    <a href="update_menu.php?id=<?php echo $menu['id']; ?>">Actualizar</a>
                    <a href="delete_menu.php?id=<?php echo $menu['id']; ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este menú?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function sortTable(columnIndex) {
    const table = document.getElementById('menusTable');
    let rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    switching = true;
    dir = "asc"; 
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[columnIndex];
            y = rows[i].getElementsByTagName("TD")[columnIndex + 1];
            if (dir === "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir === "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++; 
        } else {
            if (switchcount === 0 && dir === "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}
</script>
</body>
</html>
