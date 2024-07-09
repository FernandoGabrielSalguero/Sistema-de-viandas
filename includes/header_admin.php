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

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
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
        <li><a href="asignar_hijos.php">Gestión Usuarios</a></li>
        <li><a href="gestion_representantes.php">Gestión Representantes</a></li>
        <li><a href="alta_colegios.php">Alta Colegios</a></li>
        <li><a href="gestion_colegios.php">Gestión Colegios</a></li>
        <li><a href="alta_menu.php">Alta Menú</a></li>
        <li><a href="alta_preferencias.php">Alta Preferencias Alimenticias</a></li>
        <li><a href="logout.php">Salir</a></li>
    </ul>
</nav>
