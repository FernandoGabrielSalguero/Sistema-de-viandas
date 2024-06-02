<?php
include '../php/db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Cocina') {
    header("Location: ../views/login.php");
    exit();
}

// Consulta para obtener la cantidad de cada tipo de menú aprobado, agrupado por colegio y curso
$menusSql = "SELECT m.nombre AS menu, h.colegio, h.curso, COUNT(*) AS cantidad
             FROM pedidos p
             JOIN menus m ON p.menu_id = m.id
             JOIN hijos h ON p.hijo_id = h.id
             WHERE p.estado = 'Aprobado'
             GROUP BY m.nombre, h.colegio, h.curso";
$menusResult = $conn->query($menusSql);

// Consulta para obtener las notas especiales de los hijos
$notasSql = "SELECT DISTINCT nombre, apellido, notas, colegio, curso
             FROM hijos
             WHERE notas IS NOT NULL AND notas != ''";
$notasResult = $conn->query($notasSql);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Panel de Cocina - Viandas</title>
    <style>
        .card {
            background: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            margin: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Panel de Cocina</h1>
        <button onclick="location.href='../php/logout.php'">Cerrar sesión</button>
    </div>
    <div class="container">
        <h2>Resumen de Viandas Aprobadas</h2>
        <?php
        if ($menusResult && $menusResult->num_rows > 0) {
            while ($menu = $menusResult->fetch_assoc()) {
                echo "<div class='card'>";
                echo "<h3>{$menu['menu']}</h3>";
                echo "<p>Colegio: {$menu['colegio']}</p>";
                echo "<p>Curso: {$menu['curso']}</p>";
                echo "<p>Cantidad: {$menu['cantidad']}</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No hay pedidos aprobados para mostrar.</p>";
        }
        ?>

        <h2>Notas Especiales de los Hijos</h2>
        <?php
        if ($notasResult && $notasResult->num_rows > 0) {
            while ($nota = $notasResult->fetch_assoc()) {
                echo "<div class='card'>";
                echo "<h3>{$nota['nombre']} {$nota['apellido']}</h3>";
                echo "<p>Colegio: {$nota['colegio']}</p>";
                echo "<p>Curso: {$nota['curso']}</p>";
                echo "<p>Notas: {$nota['notas']}</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No hay notas especiales para mostrar.</p>";
        }
        ?>
    </div>
</body>
</html>
