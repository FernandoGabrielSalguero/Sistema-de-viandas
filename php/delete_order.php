<?php
// delete_order.php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Preparar y ejecutar la consulta SQL
    $sql = "DELETE FROM pedidos WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ../views/manage_orders.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}