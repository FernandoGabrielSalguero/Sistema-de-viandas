<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Usuario') {
    header('Location: ../index.php');
    exit;
}

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $hijo_id = $_POST['hijo'];
    $fecha = $_POST['fecha'];

    // Obtener el precio de la vianda
    $query_precio = "SELECT precio FROM menu WHERE fecha = '$fecha'";
    $result_precio = mysqli_query($conn, $query_precio);
    $row_precio = mysqli_fetch_assoc($result_precio);
    $precio = $row_precio['precio'];

    // Verificar el saldo del usuario
    $query_saldo = "SELECT saldo FROM usuarios WHERE id = '$usuario_id'";
    $result_saldo = mysqli_query($conn, $query_saldo);
    $row_saldo = mysqli_fetch_assoc($result_saldo);
    $saldo = $row_saldo['saldo'];

    if ($saldo >= $precio) {
        // Descontar el saldo y registrar el pedido
        $nuevo_saldo = $saldo - $precio;
        $query_actualizar_saldo = "UPDATE usuarios SET saldo = '$nuevo_saldo' WHERE id = '$usuario_id'";
        $query_registrar_pedido = "INSERT INTO pedidos (usuario_id, hijo_id, vianda, fecha, estado) VALUES ('$usuario_id', '$hijo_id', '$fecha', '$fecha', 'Procesando')";

        if (mysqli_query($conn, $query_actualizar_saldo) && mysqli_query($conn, $query_registrar_pedido)) {
            echo "Pedido realizado con éxito. Su saldo actual es: $" . $nuevo_saldo;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Saldo insuficiente para realizar el pedido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Pedido - Usuario</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <a href="seleccionar_viandas.php">Volver a Selección de Viandas</a>
    </div>
</body>
</html>
