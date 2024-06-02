<?php
include 'db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Usuario') {
    echo "<script>alert('No autorizado.'); window.location.href='login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel'])) {
    $pedidoId = $_POST['cancel'];
    $userid = $_SESSION['userid'];

    // Obtener detalles del pedido
    $sql = "SELECT menu_id, estado, fecha_pedido FROM pedidos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pedidoId);
    $stmt->execute();
    $result = $stmt->get_result();
    $pedido = $result->fetch_assoc();

    if ($pedido && $pedido['estado'] == 'Aprobado') {
        // Verificar que la cancelación sea antes de las 9 AM del día del pedido
        $fechaPedido = new DateTime($pedido['fecha_pedido']);
        $fechaActual = new DateTime();
        $horaActual = $fechaActual->format('H');

        if ($fechaPedido->format('Y-m-d') == $fechaActual->format('Y-m-d') && $horaActual < 9) {
            // Obtener el precio del menú para reembolsar al saldo
            $sql = "SELECT precio FROM menus WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pedido['menu_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $menu = $result->fetch_assoc();

            if ($menu) {
                // Actualizar el estado del pedido a 'Cancelado'
                $sql = "UPDATE pedidos SET estado = 'Cancelado' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $pedidoId);
                $stmt->execute();

                // Reembolsar el precio del menú al saldo del usuario
                $precioMenu = $menu['precio'];
                $sql = "UPDATE usuarios SET saldo = saldo + ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("di", $precioMenu, $userid);
                $stmt->execute();

                echo "<script>alert('Pedido cancelado exitosamente y saldo actualizado.'); window.location.href='user_dashboard.php';</script>";
            } else {
                echo "<script>alert('Error al obtener el precio del menú.'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('No se puede cancelar el pedido después de las 9 AM.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('El pedido no existe o ya fue cancelado.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Acción no válida.'); window.location.href='user_dashboard.php';</script>";
}