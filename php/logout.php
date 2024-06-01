<?php
// logout.php
session_start();

if (!isset($_SESSION['userid'])) {
    die("Error: No se ha iniciado sesión.");
}

session_destroy();
header("Location: ../views/login.php");
