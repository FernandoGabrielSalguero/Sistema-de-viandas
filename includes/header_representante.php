<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';
include 'functions.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'representante') {
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
