<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';
include 'functions.php';

if (!isset($_SESSION['user_id']) || getUserRole($_SESSION['user_id']) !== 'administrador') {
    header("Location: ../login.php");
    exit();
}
?>
<nav>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="gestion_pedidos.php">Gestión Pedidos</a></li>
        <li><a href="gestion_saldo.php">Gestión Saldo</a></li>
        <li><a href="alta_usuarios.php">Alta Usuarios</a></li>
        <li><a href="alta_colegios.php">Alta Colegios</a></li>
        <li><a href="gestion_colegios.php">Gestión Colegios</a></li>
        <li><a href="alta_menu.php">Alta Menú</a></li>
        <li><a href="alta_preferencias.php">Alta Preferencias Alimenticias</a></li>
        <li><a href="logout.php">Salir</a></li>
    </ul>
</nav>
