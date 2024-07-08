<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/header_papas.php';
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'papas') {
    header("Location: ../index.php");
    exit();
}

// Obtener información del usuario
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT Nombre, Correo, Saldo FROM Usuarios WHERE Id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener historial de pedidos de viandas
$stmt = $pdo->prepare("SELECT pc.Id, h.Nombre as Hijo, m.Nombre as Menú, m.Fecha_entrega, pc.Fecha_pedido, pc.Estado
                       FROM Pedidos_Comida pc
                       JOIN Hijos h ON pc.Hijo_Id = h.Id
                       JOIN `Menú` m ON pc.Menú_Id = m.Id
                       WHERE h.Usuario_Id = ?");
$stmt->execute([$usuario_id]);
$pedidos_viandas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener historial de pedidos de saldo
$stmt = $pdo->prepare("SELECT Id, Saldo, Estado, Comprobante, Fecha_pedido FROM Pedidos_Saldo WHERE Usuario_Id = ?");
$stmt->execute([$usuario_id]);
$pedidos_saldo = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Papás</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($usuario['Nombre']); ?></h1>
    <p>Correo: <?php echo htmlspecialchars($usuario['Correo']); ?></p>
    <p>Saldo disponible: <?php echo number_format($usuario['Saldo'], 2); ?> ARS</p>

    <h2>Historial de Pedidos de Viandas</h2>
    <table>
        <tr>
            <th>Hijo</th>
            <th>Menú</th>
            <th>Fecha de Entrega</th>
            <th>Fecha de Pedido</th>
            <th>Estado</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($pedidos_viandas as $pedido) : ?>
        <tr>
            <td><?php echo htmlspecialchars($pedido['Hijo']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Menú']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Fecha_entrega']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Fecha_pedido']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Estado']); ?></td>
            <td>
                <?php if ($pedido['Estado'] == 'Procesando') : ?>
                    <form method="post" action="cancelar_pedido.php">
                        <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars($pedido['Id']); ?>">
                        <button type="submit">Cancelar Pedido</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Historial de Pedidos de Saldo</h2>
    <table>
        <tr>
            <th>Saldo</th>
            <th>Estado</th>
            <th>Comprobante</th>
            <th>Fecha de Pedido</th>
        </tr>
        <?php foreach ($pedidos_saldo as $pedido) : ?>
        <tr>
            <td><?php echo number_format($pedido['Saldo'], 2); ?> ARS</td>
            <td><?php echo htmlspecialchars($pedido['Estado']); ?></td>
            <td>
                <?php if ($pedido['Comprobante']) : ?>
                    <a href="../uploads/<?php echo htmlspecialchars($pedido['Comprobante']); ?>" target="_blank">Ver Comprobante</a>
                <?php else : ?>
                    N/A
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($pedido['Fecha_pedido']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
