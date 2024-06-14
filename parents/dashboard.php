<?php
include '../common/header.php';

// Verificar si el usuario tiene el rol de padre
if ($_SESSION['role'] !== 'parent') {
    header("Location: ../login.php");
    exit();
}
?>

<div class="container">
    <h1>Dashboard del Padre</h1>
    <p>¡Bienvenido, <?php echo htmlspecialchars($user['username']); ?>!</p>
    <nav>
        <ul>
            <li><button onclick="window.location.href='dashboard.php'">Inicio</button></li>
            <li><button onclick="window.location.href='recharge.php'">Recargar Saldo</button></li>
            <!-- Agrega más enlaces según las funcionalidades disponibles -->
        </ul>
    </nav>
</div>
</body>
</html>
