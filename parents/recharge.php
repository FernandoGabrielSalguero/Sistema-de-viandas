<?php
include '../common/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['recharge'])) {
    $amount = $_POST['amount'];
    $parent_id = $_SESSION['user_id'];
    $receipt = $_FILES['receipt']['name'];

    // Guardar el archivo del comprobante
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($receipt);
    move_uploaded_file($_FILES['receipt']['tmp_name'], $target_file);

    // Insertar la recarga en la base de datos
    $stmt = $pdo->prepare("INSERT INTO recharges (parent_id, amount, receipt, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$parent_id, $amount, $receipt]);

    echo "Recarga enviada para verificación.";
}

// Obtener el historial de recargas
$stmt = $pdo->prepare("SELECT * FROM recharges WHERE parent_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$recharges = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <form action="recharge.php" method="post" enctype="multipart/form-data">
        <h2>Recargar Saldo</h2>
        <p><strong>Información Bancaria:</strong></p>
        <ul>
            <li>CBU: <span id="cbu">1234567890123456789012</span> <button type="button" onclick="copiarCBU()">Copiar CBU</button></li>
            <li>Nombre de la Cuenta: Juan Pérez</li>
            <li>Nombre del Banco: Banco Nación</li>
            <li>Alias: JUANPEREZ.BANCO</li>
            <li>CUIL: 20-12345678-9</li>
        </ul>
        <label for="amount">Monto:</label>
        <select id="amount" name="amount" required>
            <option value="3000">3000</option>
            <option value="5000">5000</option>
            <option value="7000">7000</option>
            <option value="10000">10000</option>
            <option value="15000">15000</option>
            <option value="17000">17000</option>
            <option value="20000">20000</option>
            <option value="25000">25000</option>
            <option value="30000">30000</option>
            <option value="45000">45000</option>
            <option value="55000">55000</option>
            <option value="60000">60000</option>
            <option value="75000">75000</option>
            <option value="90000">90000</option>
            <option value="100000">100000</option>
        </select>
        <label for="receipt">Comprobante:</label>
        <input type="file" id="receipt" name="receipt" required>
        <button type="submit" name="recharge">Enviar Recarga</button>
    </form>
    
    <h2>Historial de Recargas</h2>
    <table id="rechargeTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">Monto</th>
                <th onclick="sortTable(1)">Comprobante</th>
                <th onclick="sortTable(2)">Estado</th>
                <th onclick="sortTable(3)">Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recharges as $recharge): ?>
            <tr>
                <td><?php echo htmlspecialchars($recharge['amount']); ?></td>
                <td><a href="../uploads/<?php echo htmlspecialchars($recharge['receipt']); ?>" target="_blank">Ver Comprobante</a></td>
                <td><?php echo htmlspecialchars($recharge['status']); ?></td>
                <td><?php echo htmlspecialchars($recharge['created_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function copiarCBU() {
    const cbuElement = document.getElementById("cbu");
    const range = document.createRange();
    range.selectNode(cbuElement);
    window.getSelection().removeAllRanges(); 
    window.getSelection().addRange(range); 
    document.execCommand("copy");
    window.getSelection().removeAllRanges(); 
    alert("CBU copiado: " + cbuElement.textContent);
}

function sortTable(columnIndex) {
    const table = document.getElementById('rechargeTable');
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
