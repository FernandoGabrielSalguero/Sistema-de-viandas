<?php
include '../common/header.php';

$id = $_GET['id'];

// Obtener el precio del menú y el ID del padre
$stmt = $pdo->prepare("SELECT orders.menu_id, orders.parent_id, menus.price FROM orders JOIN menus ON orders.menu_id = menus.id WHERE orders.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
$menu_price = $order['price'];
$parent_id = $order['parent_id'];

// Devolver el saldo al padre
$stmt = $pdo->prepare("UPDATE parents SET saldo = saldo + ? WHERE id = ?");
$stmt->execute([$menu_price, $parent_id]);

// Cancelar el pedido en la base de datos
$stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
$stmt->execute([$id]);

header("Location: order_menu.php");
exit();