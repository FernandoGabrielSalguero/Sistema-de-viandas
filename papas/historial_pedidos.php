<?php
session_start();
include '../includes/header_papas.php';
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'papas') {
    header("Location: ../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT h.Nombre as Hijo, m.Nombre as Menu, m.Fecha_entrega, pc.Fecha_pedido, pc.Estado
                       FROM Pedidos_Comida pc
                       JOIN Hijos h ON pc.Hijo_Id = h.Id
                       JOIN Menu m ON pc.Menu_Id = m.Id
                       JOIN Usuarios_Hijos uh ON h.Id = uh.Hijo_Id
                       WHERE uh.Usuario_Id = ?");
$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Pedidos</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Historial de Pedidos</h1>
    <table>
        <tr>
            <th>Hijo</th>
            <th>Menu</th>
            <th>Fecha de Entrega</th>
            <th>Fecha de Pedido</th>
            <th>Estado</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($pedidos as $pedido) : ?>
        <tr>
            <td><?php echo htmlspecialchars($pedido['Hijo']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Menu']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Fecha_entrega']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Fecha_pedido']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Estado']); ?></td>
            <td>
                <?php if ($pedido['Estado'] == 'Procesando') : ?>
                    <form method="post" action="cancelar_pedido.php">
                        <input type="hidden" name="hijo_id" value="<?php echo htmlspecialchars($pedido['Hijo_Id']); ?>">
                        <input type="hidden" name="menu_id" value="<?php echo htmlspecialchars($pedido['Menu_Id']); ?>">
                        <button type="submit">Cancelar Pedido</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
