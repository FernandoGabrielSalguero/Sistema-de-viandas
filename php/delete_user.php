<?php
// delete_user.php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Preparar y ejecutar la consulta SQL
    $sql = "DELETE FROM usuarios WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ../views/manage_users.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}