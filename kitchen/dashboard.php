<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'Cocina') {
    header('Location: /login.php');
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

// Obtener pedidos agrupados por colegio, curso, fecha y menú
$pedidos = $pdo->query("SELECT pedidos.*, usuarios.nombre AS usuario_nombre, usuarios.apellido AS usuario_apellido, hijos.nombre AS hijo_nombre, hijos.apellido AS hijo_apellido, menus.nombre AS menu_nombre 
                        FROM pedidos 
                        JOIN usuarios ON pedidos.usuario_id = usuarios.id 
                        JOIN hijos ON pedidos.hijo_id = hijos.id 
                        JOIN menus ON pedidos.menu_id = menus.id 
                        WHERE pedidos.estado = 'Pendiente'
                        ORDER BY pedidos.fecha_pedido DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="kitchen-dashboard">
    <h1>Visualización de Pedidos</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Hijo</th>
                <th>Menú</th>
                <th>Fecha Pedido</th>
                <th>Notas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido) { ?>
            <tr>
                <td><?php echo $pedido['id']; ?></td>
                <td><?php echo $pedido['usuario_nombre'] . ' ' . $pedido['usuario_apellido']; ?></td>
                <td><?php echo $pedido['hijo_nombre'] . ' ' . $pedido['hijo_apellido']; ?></td>
                <td><?php echo $pedido['menu_nombre']; ?></td>
                <td><?php echo $pedido['fecha_pedido']; ?></td>
                <td><?php echo $pedido['notas']; ?></td>
                <td>
                    <form action="update_order.php" method="POST">
                        <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                        <button type="submit" name="action" value="complete">Completar</button>
                        <button type="submit" name="action" value="cancel">Cancelar</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>
