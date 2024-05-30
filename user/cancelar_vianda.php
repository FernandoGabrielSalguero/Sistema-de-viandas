<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Usuario') {
    header('Location: ../index.php');
    exit;
}

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pedido_id = $_POST['pedido_id'];

    // Verificar la hora actual
    $hora_actual = date('H:i');
    if ($hora_actual >= '09:00') {
        echo "No puedes cancelar la vianda después de las 9 AM.";
    } else {
        // Obtener el pedido
        $query_pedido = "SELECT * FROM pedidos WHERE id='$pedido_id' AND estado='Procesando'";
        $result_pedido = mysqli_query($conn, $query_pedido);
        $pedido = mysqli_fetch_assoc($result_pedido);

        if ($pedido) {
            // Actualizar estado del pedido
            $query_update_pedido = "UPDATE pedidos SET estado='Cancelado' WHERE id='$pedido_id'";
            mysqli_query($conn, $query_update_pedido);

            // Devolver saldo al usuario
            $usuario_id = $_SESSION['usuario_id'];
            $query_saldo = "SELECT saldo FROM usuarios WHERE id='$usuario_id'";
            $result_saldo = mysqli_query($conn, $query_saldo);
            $usuario = mysqli_fetch_assoc($result_saldo);
            $nuevo_saldo = $usuario['saldo'] + $pedido['precio'];

            $query_update_saldo = "UPDATE usuarios SET saldo='$nuevo_saldo' WHERE id='$usuario_id'";
            mysqli_query($conn, $query_update_saldo);

            echo "Vianda cancelada y saldo devuelto.";
        } else {
            echo "No se encontró el pedido o ya está cancelado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelar Vianda - Usuario</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Cancelar Vianda</h1>
        <form action="cancelar_vianda.php" method="post">
            <div class="form-group">
                <label for="pedido_id">ID del Pedido</label>
                <input type="number" id="pedido_id" name
