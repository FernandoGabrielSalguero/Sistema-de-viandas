<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $saldo = $_POST['saldo'];

    // Actualizar el saldo del usuario
    $sql = "UPDATE usuarios SET saldo = $saldo WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ../views/manage_users.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}