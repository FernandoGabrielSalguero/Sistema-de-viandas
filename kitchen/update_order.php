<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'Cocina') {
    header('Location: /login.php');
    exit;
}

include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pedido_id = $_POST['pedido_id'];
    $action = $_POST['action'];
    
    if ($action == 'complete') {
        $estado = 'Confirmado';
    } elseif ($action == 'cancel') {
        $estado = 'Cancelado';
    }
    
    $stmt = $pdo->prepare("UPDATE pedidos SET estado = :estado WHERE id = :id");
    $stmt->execute(['estado' => $estado, 'id' => $pedido_id]);
    
    header('Location: dashboard.php');
    exit;
}