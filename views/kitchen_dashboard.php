<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
    echo "Error en la consulta de hijos: " . $conn->error . "<br>"; // Debug
} else {
    while ($row = $hijosResult->fetch_assoc()) {
        $hijos[] = $row;
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
    while ($row = $pedidosResult->fetch_assoc()) {
        $pedidos[] = $row;
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
    while ($row = $kpi_result->fetch_assoc()) {
        $kpis[] = $row;
    }
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
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
        .filter-buttons {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            padding: 10px;
        }
        .filter-buttons button {
            margin: 5px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
        }
        .filter-buttons button:hover {
            background-color: #e1e1e1;
        }
        .kpi-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        .search-input {
            margin-bottom: 10px;
            padding: 8px;
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Panel de Cocina</h1>
        <button onclick="location.href='../php/logout.php'">Cerrar sesión</button>
    </div>
    <div class="filter-buttons">
        <div class="filter-group">
            <span>Colegio:</span>
            <?php foreach (array_unique(array_column($kpis, 'colegio')) as $colegio) : ?>
                <button onclick="filterKPIs('colegio', '<?= $colegio; ?>')"><?= $colegio; ?></button>
            <?php endforeach; ?>
        </div>
        <div class="filter-group">
            <span>Curso:</span>
            <?php foreach (array_unique(array_column($kpis, 'curso')) as $curso) : ?>
                <button onclick="filterKPIs('curso', '<?= $curso; ?>')"><?= $curso; ?></button>
            <?php endforeach; ?>
        </div>
        <button onclick="filterKPIs('reset')">Resetear Filtros</button>
    </div>
    <div class="container">
        <h2>Resumen de Viandas Aprobadas</h2>
        <div class="kpi-container">
            <?php foreach ($kpis as $kpi) : ?>
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
                    <th><input class="search-input" oninput="searchColumn(this, 0)" placeholder="Nombre del Hijo"></th>
                    <th><input class="search-input" oninput="searchColumn(this, 1)" placeholder="Curso"></th>
                    <th><input class="search-input" oninput="searchColumn(this, 2)" placeholder="Colegio"></th>
                    <th><input class="search-input" oninput="searchColumn(this, 3)" placeholder="Nombre del Papá"></th>
                    <th><input class="search-input" oninput="searchColumn(this, 4)" placeholder="Menú"></th>
                    <th><input class="search-input" oninput="searchColumn(this, 5)" placeholder="Fecha"></th>
                    <th><input class="search-input" oninput="searchColumn(this, 6)" placeholder="Notas"></th>
                </tr>
            </thead>
            <tbody id="pedidoTable">
                <?php foreach ($pedidos as $pedido) : ?>
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
    <script>
        function filterKPIs(filterType, filterValue) {
            var kpiCards = document.getElementsByClassName('kpi-card');
            for (var i = 0; i < kpiCards.length; i++) {
                var card = kpiCards[i];
                var colegio = card.getAttribute('data-colegio');
                var curso = card.getAttribute('data-curso');

                card.style.display = 'none'; // Oculta todas las tarjetas primero

                if (filterType === 'reset') {
                    card.style.display = 'block';
                } else if ((filterType === 'colegio' && colegio === filterValue) ||
                           (filterType === 'curso' && curso === filterValue)) {
                    card.style.display = 'block';
                }
            }
        }

        function searchColumn(input, columnIndex) {
            var filter = input.value.toUpperCase();
            var table = document.getElementById("pedidoTable");
            var tr = table.getElementsByTagName("tr");

            for (var i = 0; i < tr.length; i++) {
                var td = tr[i].getElementsByTagName("td")[columnIndex];
                if (td) {
                    var txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                } 
            }
        }
    </script>
</body>
</html>
