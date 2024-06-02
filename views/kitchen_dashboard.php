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

// Obtener los pedidos con detalles adicionales
$sql = "SELECT p.id, u.usuario AS nombre_papa, h.nombre AS nombre_hijo, h.apellido AS apellido_hijo, 
               cu.nombre AS curso, co.nombre AS colegio, h.notas, m.nombre AS menu_nombre, m.fecha, p.estado, p.fecha_pedido
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        JOIN hijos h ON p.hijo_id = h.id
        JOIN colegios co ON h.colegio_id = co.id
        JOIN cursos cu ON h.curso_id = cu.id
        JOIN menus m ON p.menu_id = m.id";
$pedidosResult = $conn->query($sql);
$pedidos = [];
if ($pedidosResult === FALSE) {
    echo "Error en la consulta de pedidos: " . $conn->error . "<br>"; // Debug
} else {
    while($row = $pedidosResult->fetch_assoc()) {
        $pedidos[] = $row;
        echo "Pedido cargado: " . $row['menu_nombre'] . " para " . $row['nombre_hijo'] . "<br>"; // Debug
    }
}

// Obtener el resumen de menús separado por colegio y curso
$sql = "SELECT co.nombre AS colegio, cu.nombre AS curso, m.nombre, COUNT(p.id) AS cantidad
        FROM pedidos p
        JOIN hijos h ON p.hijo_id = h.id
        JOIN colegios co ON h.colegio_id = co.id
        JOIN cursos cu ON h.curso_id = cu.id
        JOIN menus m ON p.menu_id = m.id
        WHERE p.estado = 'Aprobado'
        GROUP BY co.nombre, cu.nombre, m.nombre";
$kpi_result = $conn->query($sql);
$kpis = [];
if ($kpi_result === FALSE) {
    echo "Error en la consulta del resumen de menús: " . $conn->error . "<br>"; // Debug
} else {
    while($row = $kpi_result->fetch_assoc()) {
        $kpis[] = $row;
        echo "KPI cargado: " . $row['nombre'] . " - " . $row['cantidad'] . "<br>"; // Debug
    }
}


echo "Fin del script<br>"; // Debug
