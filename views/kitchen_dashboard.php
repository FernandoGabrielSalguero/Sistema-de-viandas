<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../php/db.php';
session_start();

echo "Inicio del script<br>"; // Debug

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Cocina') {
    echo "No se ha iniciado sesión o el rol no es Cocina. Redirigiendo...<br>"; // Debug
    header("Location: login.php");
    exit();
}

// Prueba una consulta simple para ver si hay problemas con la base de datos
$sql = "SELECT 1";
$result = $conn->query($sql);
if ($result === FALSE) {
    echo "Error en la consulta SQL: " . $conn->error . "<br>"; // Debug
} else {
    echo "Consulta SQL exitosa: SELECT 1<br>"; // Debug
}


// Obtener los hijos de todos los usuarios con sus colegios y cursos
$sql = "SELECT h.nombre, h.apellido, h.notas, co.nombre AS colegio, cu.nombre AS curso
        FROM hijos h
        JOIN colegios co ON h.colegio_id = co.id
        JOIN cursos cu ON h.curso_id = cu.id";
$hijosResult = $conn->query($sql);
$hijos = [];
if ($hijosResult === FALSE) {
    echo "Error en la consulta de hijos: " . $conn->error . "<br>"; // Debug
} else {
    while($row = $hijosResult->fetch_assoc()) {
        $hijos[] = $row;
        echo "Hijo cargado: " . $row['nombre'] . " " . $row['apellido'] . "<br>"; // Debug
    }
}


echo "Fin del script<br>"; // Debug
