<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'Usuario') {
    header('Location: /login.php');
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $monto = $_POST['monto'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("INSERT INTO recargas (usuario_id, monto, fecha, estado) VALUES (:usuario_id, :monto, NOW(), 'Pendiente')");
    $stmt->execute(['usuario_id' => $user_id, 'monto' => $monto]);
    
    $mensaje = "Recarga de saldo solicitada exitosamente.";
}
?>
<div class="recharge">
    <h1>Recargar Saldo</h1>
    <?php if (isset($mensaje)) { echo "<p>$mensaje</p>"; } ?>
    <form action="recharge.php" method="POST">
        <label for="monto">Monto a Recargar</label>
        <select name="monto" id="monto">
            <option value="3000">3000</option>
            <option value="5000">5000</option>
            <option value="10000">10000</option>
            <option value="15000">15000</option>
            <option value="20000">20000</option>
            <option value="30000">30000</option>
            <option value="45000">45000</option>
            <option value="55000">55000</option>
            <option value="80000">80000</option>
            <option value="100000">100000</option>
        </select>
        <button type="submit">Recargar</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
