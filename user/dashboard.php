<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'Usuario') {
    header('Location: /login.php');
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Obtener información del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$usuario = $stmt->fetch();

// Obtener hijos del usuario
$hijos = $pdo->prepare("SELECT * FROM hijos WHERE usuario_id = :usuario_id");
$hijos->execute(['usuario_id' => $user_id]);
$lista_hijos = $hijos->fetchAll(PDO::FETCH_ASSOC);

// Obtener pedidos del usuario
$pedidos = $pdo->prepare("SELECT pedidos.*, menus.nombre AS menu_nombre, menus.precio AS menu_precio 
                          FROM pedidos 
                          JOIN menus ON pedidos.menu_id = menus.id 
                          WHERE pedidos.usuario_id = :usuario_id");
$pedidos->execute(['usuario_id' => $user_id]);
$lista_pedidos = $pedidos->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="user-dashboard">
    <h1>Bienvenido, <?php echo $usuario['nombre']; ?></h1>
    <p>Saldo actual: $<?php echo number_format($usuario['saldo'], 2); ?></p>
    <a href="recharge.php">Recargar Saldo</a>
    <h2>Mis Hijos</h2>
    <ul>
        <?php foreach ($lista_hijos as $hijo) { ?>
        <li><?php echo $hijo['nombre'] . ' ' . $hijo['apellido'] . ' - ' . $hijo['curso'] . ' (' . $hijo['colegio'] . ')'; ?></li>
        <?php } ?>
    </ul>
    <h2>Mis Pedidos</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Hijo</th>
                <th>Menú</th>
                <th>Precio</th>
                <th>Fecha Pedido</th>
                <th>Notas</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lista_pedidos as $pedido) { ?>
            <tr>
                <td><?php echo $pedido['id']; ?></td>
                <td><?php echo $pedido['hijo_id']; ?></td>
                <td><?php echo $pedido['menu_nombre']; ?></td>
                <td><?php echo $pedido['menu_precio']; ?></td>
                <td><?php echo $pedido['fecha_pedido']; ?></td>
                <td><?php echo $pedido['notas']; ?></td>
                <td><?php echo $pedido['estado']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>
