<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];

    $sql = "INSERT INTO cursos (nombre) VALUES ('$nombre')";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../views/manage_cursos.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
