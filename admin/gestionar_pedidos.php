<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../index.php');
    exit;
}

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lógica para modificar el estado de un pedido
    if (isset($_POST['modificar_pedido'])) {
        $id = $_POST['id'];
        $estado = $_POST['estado'];

        $query = "UPDATE pedidos SET estado='$estado' WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            echo "Pedido modificado con éxito.";
        } else {
            echo "Error: " . $query . "<br>" . mysqli_error($conn);
        }
    }
}

// Obtener todos los pedidos
$query = "SELECT * FROM pedidos";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Pedidos - Administrador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Gestionar Pedidos</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Vianda</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['usuario_id'] ?></td>
                        <td><?= $row['vianda'] ?></td>
                        <td><?= $row['fecha'] ?></td>
                        <td><?= $row['estado'] ?></td>
                        <td>
                            <form action="gestionar_pedidos.php" method="post" style="display:inline-block;">
                                <input type="hidden" name="modificar_pedido" value="1">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <select name="estado">
                                    <option value="Procesando" <?= $row['estado'] == 'Procesando' ? 'selected' : '' ?>>Procesando</option>
                                    <option value="Aprobado" <?= $row['estado'] == 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
                                    <option value="Cancelado" <?= $row['estado'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                                <button type="submit">Modificar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="dashboard.php">Volver al Dashboard</a>
    </div>
</body>
</html>