<?php
include '../common/header.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_menu'])) {
    $date = $_POST['date'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Actualizar el menú en la base de datos
    $stmt = $pdo->prepare("UPDATE menus SET date = ?, name = ?, description = ?, price = ? WHERE id = ?");
    $stmt->execute([$date, $name, $description, $price, $id]);

    echo "El menú ha sido actualizado exitosamente.";
}

// Obtener el menú existente
$stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
$stmt->execute([$id]);
$menu = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container">
    <form action="update_menu.php?id=<?php echo $id; ?>" method="post">
        <h2>Actualizar Menú de Viandas</h2>
        <label for="date">Fecha:</label>
        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($menu['date']); ?>" required>
        <label for="name">Nombre del Menú:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($menu['name']); ?>" required>
        <label for="description">Descripción:</label>
        <textarea id="description" name="description"><?php echo htmlspecialchars($menu['description']); ?></textarea>
        <label for="price">Precio:</label>
        <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($menu['price']); ?>" step="0.01" required>
        <button type="submit" name="update_menu">Actualizar Menú</button>
    </form>
</div>
</body>
</html>
