<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $usuario_id = $_SESSION['usuario_id'];
    $hijo_id = $_POST['hijo_id'];
    $vianda_id = $_POST['vianda_id'];
    $fecha = date('Y-m-d'); // Obtener la fecha actual

    // Insertar el pedido en la base de datos
    $query = "INSERT INTO pedidos (usuario_id, hijo_id, vianda_id, fecha) 
              VALUES ('$usuario_id', '$hijo_id', '$vianda_id', '$fecha')";
    if (mysqli_query($conn, $query)) {
        $mensaje = "Pedido realizado con éxito.";
    } else {
        $mensaje = "Error al realizar el pedido: " . mysqli_error($conn);
    }
} else {
    $mensaje = "Error: Método de solicitud incorrecto.";
}

echo $mensaje;
