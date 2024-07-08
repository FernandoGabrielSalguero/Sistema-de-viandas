<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'papas') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hijo_id = $_POST['hijo_id'];
    $menu_id = $_POST['menu_id'];

    // Obtener el precio del pedido
    $stmt = $pdo->prepare("SELECT Precio FROM Menu WHERE Id = ?");
    $stmt->execute([$menu_id]);
    $precio = $stmt->fetch(PDO::FETCH_ASSOC)['Precio'];

    // Cancelar el pedido
    $stmt = $pdo->prepare("UPDATE Pedidos_Comida SET Estado = 'Cancelado' WHERE Hijo_Id = ? AND Menu_Id = ?");
    if ($stmt->execute([$hijo_id, $menu_id])) {
        // Devolver el saldo al usuario
        $usuario_id = $_SESSION['usuario_id'];
        $stmt = $pdo->prepare("UPDATE Usuarios SET Saldo = Saldo + ? WHERE Id = ?");
        $stmt->execute([$precio, $usuario_id]);
        $success = "Pedido cancelado con éxito.";
    } else {
        $error = "Error al cancelar el pedido.";
    }
    header("Location: historial_pedidos.php");
    exit();
}
