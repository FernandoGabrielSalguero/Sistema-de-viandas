<?php
session_start();
if (!isset($_SESSION['user_id']) || getUserRole($_SESSION['user_id']) !== 'papas') {
    header("Location: ../login.php");
    exit();
}
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT Nombre, Correo, Hijos, Saldo FROM Usuarios WHERE Id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<nav>
    <ul>
        <li><a href="cargar_saldo.php">Cargar Saldo</a></li>
        <li><a href="gestion_pedidos.php">Gestión Pedidos</a></li>
        <li><a href="pedir_viandas.php">Pedir Viandas</a></li>
        <li><a href="logout.php">Salir</a></li>
    </ul>
</nav>
<div class="user-info">
    <p>Nombre: <?php echo htmlspecialchars($userData['Nombre']); ?></p>
    <p>Correo: <?php echo htmlspecialchars($userData['Correo']); ?></p>
    <p>Hijos: <?php echo htmlspecialchars($userData['Hijos']); ?></p>
    <p>Saldo: $<?php echo number_format($userData['Saldo'], 2); ?></p>
</div>
