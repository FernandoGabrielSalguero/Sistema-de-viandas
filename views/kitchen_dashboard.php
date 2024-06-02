<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../php/db.php';
session_start();

echo "Inicio del script<br>"; // Debug: Eliminar después de resolver el problema

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Cocina') {
    echo "No se ha iniciado sesión o el rol no es Cocina. Redirigiendo...<br>"; // Debug
    header("Location: login.php");
    exit();
}

// Continúa con las consultas y manejo de la base de datos aquí...

echo "Fin del script<br>"; // Debug
