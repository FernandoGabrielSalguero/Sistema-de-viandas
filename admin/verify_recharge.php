<?php
include '../common/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_recharge'])) {
    $recharge_id = $_POST['recharge_id'];
    $status = $_POST['status'];

    // Actualizar el estado de la recarga
    $stmt = $pdo->prepare("UPDATE recharges SET status = ? WHERE id = ?");
    $stmt->execute([$status, $recharge_id]);

    echo "Recarga actualizada.";
}

// Obtener todas las recargas pendientes
$stmt = $pdo->prepare("SELECT recharges.*, parents.username FROM recharges JOIN parents ON recharges.parent_id = parents.id WHERE recharges.status = 'pending'");
$stmt->execute();
$recharges = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Verificar Recargas</h2>
    <table id="verifyTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">Usuario</th>
                <th onclick="sortTable(1)">Monto</th>
                <th onclick="sortTable(2)">Comprobante</th>
                <th onclick="sortTable(3)">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recharges as $recharge): ?>
            <tr>
                <td><?php echo htmlspecialchars($recharge['username']); ?></td>
                <td><?php echo htmlspecialchars($recharge['amount']); ?></td>
                <td><a href="../uploads/<?php echo htmlspecialchars($recharge['receipt']); ?>" target="_blank">Ver Comprobante</a></td>
                <td>
                    <form action="verify_recharge.php" method="post">
                        <input type="hidden" name="recharge_id" value="<?php echo $recharge['id']; ?>">
                        <select name="status">
                            <option value="approved">Aprobar</option>
                            <option value="rejected">Rechazar</option>
                        </select>
                        <button type="submit" name="verify_recharge">Actualizar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function sortTable(columnIndex) {
    const table = document.getElementById('verifyTable');
    let rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    switching = true;
    dir = "asc"; 
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[columnIndex];
            y = rows[i].getElementsByTagName("TD")[columnIndex + 1];
            if (dir === "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir === "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++; 
        } else {
            if (switchcount === 0 && dir === "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}
</script>
</body>
</html>
