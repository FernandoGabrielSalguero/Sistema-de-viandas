<?php
include 'header.php';
?>

<div class="container">
    <h2>Bienvenido, <?php echo $_SESSION['username']; ?></h2>
    <ul>
        <li><a href="manage_menus.php">Gestión de Menús</a></li>
        <li><a href="manage_users.php">Gestión de Usuarios</a></li>
        <li><a href="manage_orders.php">Gestión de Pedidos</a></li>
    </ul>
</div>
</body>
</html>
