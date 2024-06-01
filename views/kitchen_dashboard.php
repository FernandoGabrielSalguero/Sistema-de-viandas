<?php
include '../php/db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Cocina') {
    header("Location: login.php");
    exit();
}

// Obtener los hijos de todos los usuarios
$sql = "SELECT * FROM hijos";
$result = $conn->query($sql);
$hijos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $hijos[] = $row;
    }
}

// Obtener los pedidos
$sql = "SELECT pedidos.id, usuarios.usuario, hijos.nombre AS hijo_nombre, hijos.apellido AS hijo_apellido, 
               menus.nombre AS menu_nombre, pedidos.estado, pedidos.fecha_pedido
        FROM pedidos
        JOIN usuarios ON pedidos.usuario_id = usuarios.id
        JOIN hijos ON pedidos.hijo_id = hijos.id
        JOIN menus ON pedidos.menu_id = menus.id";
$result = $conn->query($sql);
$pedidos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Panel de Cocina - Viandas</title>
</head>
<body>
    <div class="header">
        <h1>Panel de Cocina</h1>
        <button onclick="location.href='../php/logout.php'">Logout</button>
    </div>
    <div class="container">
        <h2>Notas de los Hijos</h2>
        <?php if (count($hijos) > 0): ?>
            <?php foreach ($hijos as $hijo): ?>
                <p><?php echo $hijo['nombre'] . ' ' . $hijo['apellido'] . ' (Curso: ' . $hijo['curso'] . '): ' . $hijo['notas']; ?></p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay notas disponibles</p>
        <?php endif; ?>

        <h2>Pedidos Realizados</h2>
        <table class="material-design-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Hijo</th>
                    <th>Menú</th>
                    <th>Estado</th>
                    <th>Fecha de Pedido</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pedidos) > 0): ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?php echo $pedido['id']; ?></td>
                            <td><?php echo $pedido['usuario']; ?></td>
                            <td><?php echo $pedido['hijo_nombre'] . ' ' . $pedido['hijo_apellido']; ?></td>
                            <td><?php echo $pedido['menu_nombre']; ?></td>
                            <td><?php echo $pedido['estado']; ?></td>
                            <td><?php echo $pedido['fecha_pedido']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No hay pedidos realizados</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
