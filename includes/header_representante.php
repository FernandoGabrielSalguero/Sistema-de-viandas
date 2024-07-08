<?php
session_start();
if (!isset($_SESSION['user_id']) || getUserRole($_SESSION['user_id']) !== 'representante') {
    header("Location: ../login.php");
    exit();
}
?>
<nav>
    <ul>
        <li><a href="pedidos.php">Pedidos</a></li>
        <li><a href="logout.php">Salir</a></li>
    </ul>
</nav>
