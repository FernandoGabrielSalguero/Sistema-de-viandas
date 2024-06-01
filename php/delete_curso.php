<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM cursos WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../views/manage_cursos.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "ID del curso no especificado.";
}