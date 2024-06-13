<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: /login.php');
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Código para aprobar o rechazar recargas de saldo
}

$recargas = $pdo->query("SELECT * FROM recargas")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="manage-recharges">
    <h1>Gestionar Recargas de Saldo</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario ID</th>
                <th>Monto</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recargas as $recarga) { ?>
            <tr>
                <td><?php echo $recarga['id']; ?></td>
                <td><?php echo $recarga['usuario_id']; ?></td>
                <td><?php echo $recarga['monto']; ?></td>
                <td><?php echo $recarga['fecha']; ?></td>
                <td><?php echo $recarga['estado']; ?></td>
                <td>
                    <form action="manage_recharges.php" method="POST">
                        <input type="hidden" name="recarga_id" value="<?php echo $recarga['id']; ?>">
                        <button type="submit" name="action" value="approve">Aprobar</button>
                        <button type="submit" name="action" value="reject">Rechazar</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>
