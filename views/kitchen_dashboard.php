<?php
include '../php/db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Cocina') {
    header("Location: login.php");
    exit();
}

// Obtener los hijos de todos los usuarios
$sql = "SELECT * FROM hijos";
$result = $conn->query($sql);
$hijos = [];
if ($result === FALSE) {
    die("<script>console.error('Error en la consulta de hijos: " . $conn->error . "');</script>");
}
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $hijos[] = $row;
    }
}

// Obtener los pedidos con detalles adicionales
$sql = "SELECT pedidos.id, usuarios.usuario AS nombre_papa, hijos.nombre AS nombre_hijo, hijos.apellido AS apellido_hijo, 
               hijos.curso, hijos.colegio, hijos.notas, menus.nombre AS menu_nombre, menus.fecha, pedidos.estado, pedidos.fecha_pedido
        FROM pedidos
        JOIN usuarios ON pedidos.usuario_id = usuarios.id
        JOIN hijos ON pedidos.hijo_id = hijos.id
        JOIN menus ON pedidos.menu_id = menus.id";
$result = $conn->query($sql);
$pedidos = [];
if ($result === FALSE) {
    die("<script>console.error('Error en la consulta de pedidos: " . $conn->error . "');</script>");
}
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
}

// Obtener el resumen de menús separado por colegio y curso
$sql = "SELECT hijos.colegio, hijos.curso, menus.nombre, COUNT(pedidos.id) AS cantidad
        FROM pedidos
        JOIN hijos ON pedidos.hijo_id = hijos.id
        JOIN menus ON pedidos.menu_id = menus.id
        GROUP BY hijos.colegio, hijos.curso, menus.nombre";
$kpi_result = $conn->query($sql);
$kpis = [];
if ($kpi_result === FALSE) {
    die("<script>console.error('Error en la consulta del resumen de menús: " . $conn->error . "');</script>");
}
if ($kpi_result->num_rows > 0) {
    while($row = $kpi_result->fetch_assoc()) {
        $kpis[] = $row;
    }
}

// Obtener listas de colegios y cursos para los filtros
$colegios = array_unique(array_column($kpis, 'colegio'));
$cursos = array_unique(array_column($kpis, 'curso'));
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
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin: 10px;
            text-align: center;
            flex: 1;
        }
        .kpi-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .filter-buttons {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .filter-buttons button {
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Panel de Cocina</h1>
        <button onclick="location.href='../php/logout.php'">Logout</button>
    </div>
    <div class="container">
        <h2>Notas de los Hijos</h2>
        <?php if (count($hijos) > 0): ?>
            <?php foreach ($hijos as $hijo): ?>
                <p><?php echo $hijo['nombre'] . ' ' . $hijo['apellido'] . ' (Curso: ' . $hijo['curso'] . ' - Colegio: ' . $hijo['colegio'] . '): ' . $hijo['notas']; ?></p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay notas disponibles</p>
        <?php endif; ?>

        <h2>Resumen de Menús</h2>
        <div class="filter-buttons">
            <button onclick="filterKPIs('')">Todos</button>
            <?php foreach ($colegios as $colegio): ?>
                <button onclick="filterKPIs('<?php echo $colegio; ?>')"><?php echo $colegio; ?></button>
            <?php endforeach; ?>
        </div>
        <div class="kpi-container" id="kpi-container">
            <?php foreach ($kpis as $kpi): ?>
                <div class="kpi-card" data-colegio="<?php echo $kpi['colegio']; ?>">
                    <h3><?php echo $kpi['nombre']; ?></h3>
                    <p>Colegio: <?php echo $kpi['colegio']; ?></p>
                    <p>Curso: <?php echo $kpi['curso']; ?></p>
                    <p>Cantidad: <?php echo $kpi['cantidad']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <h2>Pedidos Realizados</h2>
        <input type="text" id="filter" placeholder="Filtrar pedidos..." onkeyup="filterTable()">
        <table class="material-design-table" id="pedidos-table">
            <thead>
                <tr>
                    <th>Nombre Alumno</th>
                    <th>Curso</th>
                    <th>Colegio</th>
                    <th>Nombre Papá</th>
                    <th>Menú</th>
                    <th>Día</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pedidos) > 0): ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?php echo $pedido['nombre_hijo'] . ' ' . $pedido['apellido_hijo']; ?></td>
                            <td><?php echo $pedido['curso']; ?></td>
                            <td><?php echo $pedido['colegio']; ?></td>
                            <td><?php echo $pedido['nombre_papa']; ?></td>
                            <td><?php echo $pedido['menu_nombre']; ?></td>
                            <td><?php echo $pedido['fecha']; ?></td>
                            <td><?php echo $pedido['notas']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No hay pedidos realizados</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script>
    function filterKPIs(colegio) {
        var cards = document.getElementsByClassName('kpi-card');
        for (var i = 0; i < cards.length; i++) {
            if (colegio === '' || cards[i].getAttribute('data-colegio') === colegio) {
                cards[i].style.display = 'block';
            } else {
                cards[i].style.display = 'none';
            }
        }
    }

    function filterTable() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById('filter');
        filter = input.value.toLowerCase();
        table = document.getElementById('pedidos-table');
        tr = table.getElementsByTagName('tr');
        for (i = 1; i < tr.length; i++) {
            tr[i].style.display = 'none';
            td = tr[i].getElementsByTagName('td');
            for (j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = '';
                        break;
                    }
                }
            }
        }
    }
    </script>
</body>
</html>
