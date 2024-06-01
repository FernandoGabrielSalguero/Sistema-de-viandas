<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

// Handle form submission to add or edit menu items
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $date = $_POST['date'];
    $id = $_POST['id'];

    if ($id) {
        // Update existing menu item
        $query = "UPDATE menu SET name = ?, price = ?, date = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdsi", $name, $price, $date, $id);
    } else {
        // Insert new menu item
        $query = "INSERT INTO menu (name, price, date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sds", $name, $price, $date);
    }
    $stmt->execute();
    header("Location: manage_menu.php");
    exit();
}

// Fetch menu items from the database
$query = "SELECT * FROM menu";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Menú</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_users.php">Gestionar Usuarios</a>
        <a href="manage_menu.php">Gestionar Menú</a>
        <a href="manage_orders.php">Gestionar Pedidos</a>
        <a href="../logout.php">Cerrar Sesión</a>
    </div>
    <div class="main-content">
        <h2>Gestionar Menú</h2>
        <form action="manage_menu.php" method="POST">
            <input type="hidden" name="id" value="">
            <label for="name">Nombre de la Vianda:</label>
            <input type="text" id="name" name="name" required>
            <label for="price">Precio:</label>
            <input type="number" step="0.01" id="price" name="price" required>
            <label for="date">Fecha:</label>
            <input type="date" id="date" name="date" required>
            <button type="submit">Guardar</button>
        </form>
        <table>
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
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['price'] ?></td>
                        <td><?= $row['date'] ?></td>
                        <td>
                            <a href="edit_menu.php?id=<?= $row['id'] ?>">Editar</a>
                            <a href="delete_menu.php?id=<?= $row['id'] ?>">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
