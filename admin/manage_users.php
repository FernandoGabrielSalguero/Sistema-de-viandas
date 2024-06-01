<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

// Fetch users from the database
$query = "SELECT * FROM users";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios</title>
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
        <h2>Gestionar Usuarios</h2>
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
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['surname'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td><?= $row['phone'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['role'] ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $row['id'] ?>">Editar</a>
                            <a href="delete_user.php?id=<?= $row['id'] ?>">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
