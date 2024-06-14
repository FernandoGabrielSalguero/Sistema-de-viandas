<?php
include '../common/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_recharge'])) {
    $recharge_id = $_POST['recharge_id'];

    // Obtener la recarga
    $stmt = $pdo->prepare("SELECT * FROM recharges WHERE id = ?");
    $stmt->execute([$recharge_id]);
    $recharge = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($recharge && $recharge['status'] == 'pending') {
        $parent_id = $recharge['parent_id'];
        $amount = $recharge['amount'];

        // Actualizar el saldo del padre
        $stmt = $pdo->prepare("UPDATE parents SET saldo = saldo + ? WHERE id = ?");
        $stmt->execute([$amount, $parent_id]);

        // Marcar la recarga como aprobada
        $stmt = $pdo->prepare("UPDATE recharges SET status = 'approved' WHERE id = ?");
        $stmt->execute([$recharge_id]);

        $message = "La recarga ha sido aprobada exitosamente.";
    } else {
        $message = "Error: No se pudo aprobar la recarga.";
    }
}

// Obtener todas las recargas pendientes
$stmt = $pdo->prepare("SELECT * FROM recharges WHERE status = 'pending'");
$stmt->execute();
$recharges = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div id="toast" class="toast"><?php echo $message; ?></div>
    <h2>Verificar Recargas</h2>
    <table id="rechargesTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Padre</th>
                <th>Monto</th>
                <th>Comprobante</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recharges as $recharge): ?>
            <tr>
                <td><?php echo htmlspecialchars($recharge['id']); ?></td>
                <td><?php echo htmlspecialchars($recharge['parent_id']); ?></td>
                <td><?php echo htmlspecialchars($recharge['amount']); ?></td>
                <td><a href="../uploads/<?php echo htmlspecialchars($recharge['receipt']); ?>" target="_blank">Ver Comprobante</a></td>
                <td>
                    <form action="verify_recharge.php" method="post">
                        <input type="hidden" name="recharge_id" value="<?php echo $recharge['id']; ?>">
                        <button type="submit" name="approve_recharge">Aprobar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function showToast(message) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.className = "toast show";
    setTimeout(function() {
        toast.className = toast.className.replace("show", "");
    }, 5000);
}

<?php if (isset($message) && $message): ?>
    showToast("<?php echo $message; ?>");
<?php endif; ?>
</script>
</body>
</html>
