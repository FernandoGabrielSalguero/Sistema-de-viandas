<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['userid'];
    $hijo_id = $_POST['hijo_id'];
    $menus = $_POST['menu_id'];

    // Calcular el total del pedido
    $total = 0;
    foreach ($menus as $fecha => $menu_id) {
        if (!empty($menu_id)) {
            $sql = "SELECT precio FROM menus WHERE id = $menu_id";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $total += $result->fetch_assoc()['precio'];
            }
        }
    }

    // Obtener el saldo del usuario
    $sql = "SELECT saldo FROM usuarios WHERE id = $usuario_id";
    $saldo_result = $conn->query($sql);
    $saldo = 0;
    if ($saldo_result->num_rows > 0) {
        $saldo = $saldo_result->fetch_assoc()['saldo'];
    }

    // Calcular el saldo restante y el monto a transferir
    $saldoUtilizado = min($total, $saldo);
    $montoRestante = $total - $saldoUtilizado;

    // Actualizar el saldo del usuario
    $nuevoSaldo = $saldo - $saldoUtilizado;
    $sql = "UPDATE usuarios SET saldo = $nuevoSaldo WHERE id = $usuario_id";
    $conn->query($sql);

    // Iniciar la transacción
    $conn->begin_transaction();

    try {
        foreach ($menus as $fecha => $menu_id) {
            if (!empty($menu_id)) {
                $sql = "INSERT INTO pedidos (usuario_id, hijo_id, menu_id, estado) 
                        VALUES ('$usuario_id', '$hijo_id', '$menu_id', 'En espera de aprobación')";
                $conn->query($sql);
            }
        }

        // Confirmar la transacción
        $conn->commit();
        header("Location: ../views/user_dashboard.php");
    } catch (Exception $e) {
        // Revertir la transacción
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}