<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Usuario') {
    header('Location: ../index.php');
    exit;
}

include '../includes/db_connect.php';

$usuario_id = $_SESSION['usuario_id'];
$query = "SELECT * FROM pedidos WHERE usuario_id='$usuario_id'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pedidos - Usuario</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Historial de Pedidos</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vianda</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['vianda'] ?></td>
                        <td><?= $row['fecha'] ?></td>
                        <td><?= $row['estado'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="dashboard_usuario.php">Volver al Dashboard</a>
    </div>
</body>
</html>
