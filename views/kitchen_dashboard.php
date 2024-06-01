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
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $hijos[] = $row;
    }
}

// Obtener los pedidos con detalles adicionales
$sql = "SELECT pedidos.id, usuarios.usuario AS nombre_papa, hijos.nombre AS nombre_hijo, hijos.apellido AS apellido_hijo, hijos.curso, hijos.notas, 
               menus.nombre AS menu_nombre, menus.fecha, pedidos.estado, pedidos.fecha_pedido
        FROM pedidos
        JOIN usuarios ON pedidos.usuario_id = usuarios.id
        JOIN hijos ON pedidos.hijo_id = hijos.id
        JOIN menus ON pedidos.menu_id = menus.id";
$result = $conn->query($sql);
$pedidos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
}

// Obtener el resumen de menús
$sql = "SELECT menus.nombre, COUNT(pedidos.id) AS cantidad
        FROM pedidos
        JOIN menus ON pedidos.menu_id = menus.id
        GROUP BY menus.nombre";
$kpi_result = $conn->query($sql);
$kpis = [];
if ($kpi_result->num_rows > 0) {
    while($row = $kpi_result->fetch_assoc()) {
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
                <p><?php echo $hijo['nombre'] . ' ' . $hijo['apellido'] . ' (Curso: ' . $hijo['curso'] . '): ' . $hijo['notas']; ?></p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay notas disponibles</p>
        <?php endif; ?>

        <h2>Resumen de Menús</h2>
        <div class="kpi-container">
            <?php foreach ($kpis as $kpi): ?>
                <div class="kpi-card">
                    <h3><?php echo $kpi['nombre']; ?></h3>
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
                            <td><?php echo $pedido['nombre_papa']; ?></td>
                            <td><?php echo $pedido['menu_nombre']; ?></td>
                            <td><?php echo $pedido['fecha']; ?></td>
                            <td><?php echo $pedido['notas']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No hay pedidos realizados</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script>
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
