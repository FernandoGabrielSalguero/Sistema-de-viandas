<?php
session_start();
include '../includes/db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $usuario_id = $_SESSION['usuario_id'];
    $hijo_id = $_POST['hijo_id'];
    $vianda_id = $_POST['vianda_id'];
    $fecha = date('Y-m-d'); // Obtener la fecha actual
    $notas = $_POST['notas']; // Obtener las notas del pedido

    // Verificar si el ID de la vianda existe en la tabla menu
    $query_verificar = "SELECT id FROM menu WHERE id = '$vianda_id'";
    $result_verificar = mysqli_query($conn, $query_verificar);

    if (mysqli_num_rows($result_verificar) > 0) {
        // Insertar el pedido en la base de datos, incluyendo las notas
        $query = "INSERT INTO pedidos (usuario_id, hijo_id, vianda_id, fecha, notas) 
                  VALUES ('$usuario_id', '$hijo_id', '$vianda_id', '$fecha', '$notas')";
        if (mysqli_query($conn, $query)) {
            $mensaje = "Pedido realizado con éxito.";
        } else {
            $mensaje = "Error al realizar el pedido: " . mysqli_error($conn);
        }
    } else {
        $mensaje = "Error: La vianda seleccionada no existe.";
    }
} else {
    $mensaje = "Error: Método de solicitud incorrecto.";
}

echo $mensaje;

