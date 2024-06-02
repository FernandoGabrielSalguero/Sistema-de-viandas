<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../php/db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Cocina') {
    header("Location: login.php");
    exit();
}

// Consultas SQL para obtener datos
$hijos_sql = "SELECT h.nombre, h.apellido, h.notas, co.nombre AS colegio, cu.nombre AS curso
              FROM hijos h
              JOIN colegios co ON h.colegio_id = co.id
              JOIN cursos cu ON h.curso_id = cu.id";
$hijosResult = $conn->query($hijos_sql);
$hijos = [];
while ($row = $hijosResult->fetch_assoc()) {
    $hijos[] = $row;
}

$pedidos_sql = "SELECT p.id, u.usuario AS nombre_papa, h.nombre AS nombre_hijo, h.apellido AS apellido_hijo, 
                    cu.nombre AS curso, co.nombre AS colegio, h.notas, m.nombre AS menu_nombre, m.fecha, p.estado, p.fecha_pedido
                FROM pedidos p
                JOIN usuarios u ON p.usuario_id = u.id
                JOIN hijos h ON p.hijo_id = h.id
                JOIN colegios co ON h.colegio_id = co.id
                JOIN cursos cu ON h.curso_id = cu.id
                JOIN menus m ON p.menu_id = m.id";
$pedidosResult = $conn->query($pedidos_sql);
$pedidos = [];
while ($row = $pedidosResult->fetch_assoc()) {
    $pedidos[] = $row;
}

$kpi_sql = "SELECT co.nombre AS colegio, cu.nombre AS curso, m.nombre, COUNT(p.id) AS cantidad
            FROM pedidos p
            JOIN hijos h ON p.hijo_id = h.id
            JOIN colegios co ON h.colegio_id = co.id
            JOIN cursos cu ON h.curso_id = cu.id
            JOIN menus m ON p.menu_id = m.id
            WHERE p.estado = 'Aprobado'
            GROUP BY co.nombre, cu.nombre, m.nombre";
$kpi_result = $conn->query($kpi_sql);
$kpis = [];
while ($row = $kpi_result->fetch_assoc()) {
    $kpis[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Panel de Cocina - Viandas</title>
    <style>
        .kpi-card {
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            margin: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            display: inline-block;
            width: 200px;
        }
        .material-design-table {
            width: 100%;
            border-collapse: collapse;
        }
        .material-design-table th, .material-design-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .material-design-table th {
            background-color: #f2f2f2;
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
        <div class="kpi-container">
            <?php foreach ($kpis as $kpi): ?>
                <div class="kpi-card" data-colegio="<?= $kpi['colegio']; ?>" data-curso="<?= $kpi['curso']; ?>" data-menu="<?= $kpi['nombre']; ?>">
                    <h4><?= $kpi['nombre']; ?></h4>
                    <p>Colegio: <?= $kpi['colegio']; ?></p>
                    <p>Curso: <?= $kpi['curso']; ?></p>
                    <p>Cantidad: <?= $kpi['cantidad']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <h2>Pedidos Realizados</h2>
        <table class="material-design-table">
            <thead>
                <tr>
                    <th>Nombre del Hijo</th>
                    <th>Curso</th>
                    <th>Colegio</th>
                    <th>Nombre del Papá</th>
                    <th>Menú</th>
                    <th>Fecha</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?= $pedido['nombre_hijo'] . " " . $pedido['apellido_hijo']; ?></td>
                        <td><?= $pedido['curso']; ?></td>
                        <td><?= $pedido['colegio']; ?></td>
                        <td><?= $pedido['nombre_papa']; ?></td>
                        <td><?= $pedido['menu_nombre']; ?></td>
                        <td><?= $pedido['fecha']; ?></td>
                        <td><?= $pedido['notas']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
