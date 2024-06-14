<?php
include '../common/header.php';

$id = $_GET['id'];

// Eliminar el menú de la base de datos
$stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
$stmt->execute([$id]);

header("Location: create_menu.php");
exit();