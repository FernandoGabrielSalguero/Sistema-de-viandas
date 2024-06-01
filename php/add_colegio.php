<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];

    $sql = "INSERT INTO colegios (nombre) VALUES ('$nombre')";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../views/manage_colegios.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}