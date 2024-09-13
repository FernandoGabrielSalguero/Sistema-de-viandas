<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_agencia') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';
include '../includes/functions.php'; // Asegúrate de que tienes una función para enviar correos

// Obtener los datos del formulario
$destino_id = $_POST['destino'];
$hora_salida = $_POST['hora_salida'];
$productos = $_POST['productos'];
$fecha_pedido = date('Y-m-d');
$estado = 'vigente';

// Obtener el hyt_admin asignado a esta agencia
$agencia_id = $_SESSION['usuario_id'];
$stmt_admin = $pdo->prepare("SELECT hyt_admin_id FROM hyt_admin_agencia WHERE hyt_agencia_id = ?");
$stmt_admin->execute([$agencia_id]);
$hyt_admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);
$hyt_admin_id = $hyt_admin['hyt_admin_id'];

// Insertar el pedido en la tabla pedidos_hyt
$stmt_pedido = $pdo->prepare("INSERT INTO pedidos_hyt (nombre_agencia, fecha_pedido, hora_salida, estado, destino_id, hyt_admin_id) 
                              VALUES (?, ?, ?, ?, ?, ?)");
$stmt_pedido->execute([$agencia_id, $fecha_pedido, $hora_salida, $estado, $destino_id, $hyt_admin_id]);
$pedido_id = $pdo->lastInsertId(); // Obtener el ID del pedido recién creado

// Insertar el detalle del pedido en la tabla detalle_pedidos_hyt
$stmt_detalle = $pdo->prepare("INSERT INTO detalle_pedidos_hyt (pedido_id, nombre, precio, cantidad) VALUES (?, ?, ?, ?)");
foreach ($productos as $producto_id => $cantidad) {
    if ($cantidad > 0) {
        // Obtener los detalles del producto (nombre y precio)
        $stmt_producto = $pdo->prepare("SELECT nombre, precio FROM precios_hyt WHERE id = ?");
        $stmt_producto->execute([$producto_id]);
        $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);
        
        // Insertar el detalle del pedido
        $stmt_detalle->execute([$pedido_id, $producto['nombre'], $producto['precio'], $cantidad]);
    }
}

// Enviar correo electrónico con el detalle del pedido
$subject = "Detalle del Pedido Realizado";
$message = "Se ha realizado un pedido con los siguientes detalles:\n\n";
foreach ($productos as $producto_id => $cantidad) {
    if ($cantidad > 0) {
        $message .= "{$producto['nombre']}: $cantidad\n";
    }
}
$to = "email@cliente.com"; // Cambia esto por el correo del cliente
enviarCorreo($to, $subject, $message);

header("Location: confirmar_pedido.php?status=success");
exit();
