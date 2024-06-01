<?php
// delete_menu.php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Preparar y ejecutar la consulta SQL
    $sql = "DELETE FROM menus WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: ../views/admin_dashboard.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
