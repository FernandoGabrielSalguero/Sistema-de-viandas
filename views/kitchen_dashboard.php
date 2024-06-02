<?php
include '../php/db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Cocina') {
    header("Location: login.php");
    exit();
}

// Obtener los hijos de todos los usuarios con sus colegios y cursos
$sql = "SELECT h.nombre, h.apellido, h.notas, co.nombre AS colegio, cu.nombre AS curso
        FROM hijos h
        JOIN colegios co ON h.colegio_id = co.id
        JOIN cursos cu ON h.curso_id = cu.id";
$hijosResult = $conn->query($sql);
$hijos = [];
if ($hijosResult === FALSE) {
    die("<script>console.error('Error en la consulta de hijos: " . $conn->error . "');</script>");
}
while($row = $hijosResult->fetch_assoc()) {
    $hijos[] = $row;
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
    die("<script>console.error('Error en la consulta de pedidos: " . $conn->error . "');</script>");
}
while($row = $pedidosResult->fetch_assoc()) {
    $pedidos[] = $row;
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
    die("<script>console.error('Error en la consulta del resumen de menús: " . $conn->error . "');</script>");
}
while($row = $kpi_result->fetch_assoc()) {
    $kpis[] = $row;
}

// Obtener listas de colegios y cursos para los filtros
$colegios = array_unique(array_column($kpis, 'colegio'));
$cursos = array_unique(array_column($kpis, 'curso'));