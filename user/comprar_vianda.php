<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Usuario') {
    header('Location: ../index.php');
    exit;
}

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hijo_id = $_POST['hijo_id'];
    $fecha = $_POST['fecha'];

    // Obtener el menú seleccionado
    $query_menu = "SELECT * FROM menu WHERE fecha='$fecha'";
    $result_menu = mysqli_query($conn, $query_menu);
    $menu = mysqli_fetch_assoc($result_menu);

    // Obtener saldo del usuario
    $usuario_id = $_SESSION['usuario_id'];
    $query_saldo = "SELECT saldo FROM usuarios WHERE id='$usuario_id'";
    $result_saldo = mysqli_query($conn, $query_saldo);
    $usuario = mysqli_fetch_assoc($result_saldo);
    $saldo = $usuario['saldo'];

    // Verificar saldo
    if ($saldo >= $menu['precio']) {
        $nuevo_saldo = $saldo - $menu['precio'];
        $query_update_saldo = "UPDATE usuarios SET saldo='$nuevo_saldo' WHERE id='$usuario_id'";
        mysqli_query($conn, $query_update_saldo);

        // Insertar pedido
        $query_insert_pedido = "INSERT INTO pedidos (usuario_id, hijo_id, vianda, fecha, estado) VALUES ('$usuario_id', '$hijo_id', '{$menu['nombre']}', '$fecha', 'Procesando')";
        mysqli_query($conn, $query_insert_pedido);

        echo "Compra realizada con éxito.";
    } else {
        echo "Saldo insuficiente. Por favor recargue su saldo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Vianda - Usuario</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Comprar Vianda</h1>
        <p><a href="seleccionar_viandas.php">Volver a seleccionar viandas</a></p>
    </div>
</body>
</html>
