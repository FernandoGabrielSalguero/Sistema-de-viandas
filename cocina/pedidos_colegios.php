<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_cocina.php';
include '../includes/db.php';

// Procesar el formulario de filtro por fecha de entrega y colegio
$fecha_filtro = '';
$colegio_filtro = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filtrar_fecha'])) {
    $fecha_filtro = $_POST['fecha_entrega'];
    $colegio_filtro = $_POST['colegio'];
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_filtro'])) {
    $fecha_filtro = '';
    $colegio_filtro = '';
}

// Obtener la cantidad total de viandas compradas, agrupadas por nombre de menú y día de entrega
$query_menus = "
    SELECT m.Nombre AS MenuNombre, DATE_FORMAT(pc.Fecha_entrega, '%d/%m/%y') AS FechaEntrega, COUNT(*) AS Cantidad
    FROM Pedidos_Comida pc
    JOIN Menú m ON pc.Menú_Id = m.Id
    WHERE pc.Estado = 'Procesando'
";
if (!empty($fecha_filtro) || !empty($colegio_filtro)) {
    $query_menus .= " AND ";
    if (!empty($fecha_filtro)) {
        $query_menus .= "pc.Fecha_entrega = ? ";
    }
    if (!empty($fecha_filtro) && !empty($colegio_filtro)) {
        $query_menus .= "AND ";
    }
    if (!empty($colegio_filtro)) {
        $query_menus .= "h.Colegio_Id = ? ";
    }
}
$query_menus .= " GROUP BY m.Nombre, pc.Fecha_entrega";
$stmt = $pdo->prepare($query_menus);
$params = [];
if (!empty($fecha_filtro)) {
    $params[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $params[] = $colegio_filtro;
}
$stmt->execute($params);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la cantidad total de viandas compradas, divididas por colegio y cursos
$query_colegios = "
    SELECT c.Nombre AS ColegioNombre, cu.Nombre AS CursoNombre, m.Nombre AS MenuNombre, COUNT(*) AS Cantidad, DATE_FORMAT(pc.Fecha_entrega, '%d/%m/%y') AS FechaEntrega
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Colegios c ON h.Colegio_Id = c.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
    WHERE pc.Estado = 'Procesando'
";
if (!empty($fecha_filtro) || !empty($colegio_filtro)) {
    $query_colegios .= " AND ";
    if (!empty($fecha_filtro)) {
        $query_colegios .= "pc.Fecha_entrega = ? ";
    }
    if (!empty($fecha_filtro) && !empty($colegio_filtro)) {
        $query_colegios .= "AND ";
    }
    if (!empty($colegio_filtro)) {
        $query_colegios .= "h.Colegio_Id = ? ";
    }
}
$query_colegios .= " GROUP BY c.Nombre, cu.Nombre, m.Nombre, pc.Fecha_entrega";
$stmt = $pdo->prepare($query_colegios);
$params = [];
if (!empty($fecha_filtro)) {
    $params[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $params[] = $colegio_filtro;
}
$stmt->execute($params);
$colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener los alumnos con preferencias alimenticias
$query_preferencias = "
    SELECT m.Nombre AS MenuNombre, DATE_FORMAT(pc.Fecha_entrega, '%d/%m/%y') AS FechaEntrega, 
           c.Nombre AS ColegioNombre, cu.Nombre AS CursoNombre, 
           h.Nombre AS AlumnoNombre, p.Nombre AS PreferenciaNombre
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Colegios c ON h.Colegio_Id = c.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
    JOIN Preferencias_Alimenticias p ON pc.Preferencias_alimenticias = p.Id
    WHERE pc.Estado = 'Procesando' 
    AND pc.Preferencias_alimenticias IS NOT NULL
    AND p.Nombre != 'Sin preferencias'
";
if (!empty($fecha_filtro)) {
    $query_preferencias .= " AND pc.Fecha_entrega = ?";
}
$stmt = $pdo->prepare($query_preferencias);
$params = [];
if (!empty($fecha_filtro)) {
    $params[] = $fecha_filtro;
}
$stmt->execute($params);
$preferencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar los datos por nivel y menú
$niveles = [
    'Nivel Inicial' => ['Nivel Inicial Sala 3A', 'Nivel Inicial Sala 3B', 'Nivel Inicial Sala 4A', 'Nivel Inicial Sala 4B', 'Nivel Inicial Sala 4C', 'Nivel Inicial Sala 5A', 'Nivel Inicial Sala 5B'],
    'Primaria' => ['Primaria Primer Grado A', 'Primaria Primer Grado B', 'Primaria Segundo Grado', 'Primaria Segundo Grado B', 'Primaria Tercer Grado', 'Primaria cuarto Grado', 'Primaria Quinto Grado', 'Primaria Sexto Grado', 'Primaria Septimo Grado'],
    'Secundaria' => ['Secundaria Primer Año', 'Secundaria Segundo Año', 'Secundaria Tercer año']
];

$niveles_data = [];
foreach ($colegios as $colegio) {
    foreach ($niveles as $nivel => $cursos) {
        if (in_array($colegio['CursoNombre'], $cursos)) {
            if (!isset($niveles_data[$nivel])) {
                $niveles_data[$nivel] = [];
            }
            $key = $colegio['MenuNombre'] . '-' . $colegio['FechaEntrega'];
            if (!isset($niveles_data[$nivel][$key])) {
                $niveles_data[$nivel][$key] = [
                    'ColegioNombre' => $colegio['ColegioNombre'],
                    'MenuNombre' => $colegio['MenuNombre'],
                    'Cantidad' => 0,
                    'FechaEntrega' => $colegio['FechaEntrega']
                ];
            }
            $niveles_data[$nivel][$key]['Cantidad'] += $colegio['Cantidad'];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Cocina</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .kpi-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .kpi {
            border: 1px solid #000;
            border-radius: 5px;
            padding: 20px;
            margin: 10px;
            text-align: center;
            width: 200px;
        }

        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-item {
            flex: 1 1 200px;
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <h1>Dashboard Cocina</h1>

    <form method="post" action="pedidos_colegios.php" class="filter-container">
        <div class="filter-item">
            <label for="fecha_entrega">Filtrar por Fecha de Entrega:</label>
            <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($fecha_filtro); ?>">
        </div>
        <div class="filter-item">
            <label for="colegio">Filtrar por Colegio:</label>
            <input type="text" id="colegio" name="colegio" value="<?php echo htmlspecialchars($colegio_filtro); ?>">
        </div>
        <div class="filter-item">
            <button type="submit" name="filtrar_fecha">Filtrar</button>
        </div>
        <div class="filter-item">
            <button type="submit" name="eliminar_filtro">Eliminar Filtro</button>
        </div>
    </form>

    <h2>Total de Menús</h2>
    <div class="kpi-container">
        <?php
        $total_viandas = 0;
        foreach ($menus as $menu) :
            $total_viandas += $menu['Cantidad'];
        ?>
            <div class="kpi">
                <h3><?php echo htmlspecialchars($menu['MenuNombre']); ?></h3>
                <p>Cantidad: <?php echo htmlspecialchars($menu['Cantidad']); ?></p>
                <p>Fecha de entrega: <?php echo htmlspecialchars($menu['FechaEntrega']); ?></p>
            </div>
        <?php endforeach; ?>
        <div class="kpi">
            <h3>Total</h3>
            <p><?php echo $total_viandas; ?></p>
        </div>
    </div>

    <!-- DESCARGAR MENÚ -->
    <form method="post" action="descargar_resumen.php">
        <input type="hidden" name="fecha_filtro" value="<?php echo htmlspecialchars($fecha_filtro); ?>">
        <input type="hidden" name="colegio_filtro" value="<?php echo htmlspecialchars($colegio_filtro); ?>">
        <button type="submit" name="descargar_csv">Descargar Resumen</button>
    </form>
    <!-- END DESCARGAR MENÚ -->



    <!-- Agregar botones "Ver Detalle" junto a cada nivel -->
    <h2>Totalidad de Viandas por Colegio y Nivel</h2>
    <?php foreach ($niveles_data as $nivel => $menus) : ?>
        <h3><?php echo htmlspecialchars($nivel); ?>
            <button onclick="mostrarDetalle('<?php echo htmlspecialchars($nivel); ?>')">Ver Detalle</button>
        </h3>
        <table border="1">
            <tr>
                <th>Colegio</th>
                <th>Menú</th>
                <th>Cantidad</th>
                <th>Fecha de entrega</th>
            </tr>
            <?php
            $nivel_total = 0;
            foreach ($menus as $menu) :
                $nivel_total += $menu['Cantidad'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($menu['ColegioNombre']); ?></td>
                    <td><?php echo htmlspecialchars($menu['MenuNombre']); ?></td>
                    <td><?php echo htmlspecialchars($menu['Cantidad']); ?></td>
                    <td><?php echo htmlspecialchars($menu['FechaEntrega']); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2"><strong>Total</strong></td>
                <td><strong><?php echo $nivel_total; ?></strong></td>
                <td></td>
            </tr>
        </table>
    <?php endforeach; ?>

    <!-- MODAL PARA MOSTRAR DETALLE -->
    <div id="modalDetalle" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="modalTitulo"></h2>
            <table id="tablaDetalle" border="1">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Colegio</th>
                        <th>Curso</th>
                        <th>Menú</th>
                        <th>Fecha de Entrega</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <button onclick="descargarCSV()">Descargar CSV</button>
        </div>
    </div>

    <script>
        // Datos en JSON desde PHP para poder usarlos en JS
        const pedidos = <?php echo json_encode($preferencias); ?>;

        // Función para mostrar el modal con detalles
        function mostrarDetalle(nivel) {
            document.getElementById('modalTitulo').innerText = `Detalle de ${nivel}`;
            let tbody = document.querySelector("#tablaDetalle tbody");
            tbody.innerHTML = '';

            let pedidosFiltrados = pedidos.filter(p => p.CursoNombre.includes(nivel));

            pedidosFiltrados.forEach(pedido => {
                let fila = `<tr>
                        <td>${pedido.AlumnoNombre}</td>
                        <td>${pedido.ColegioNombre}</td>
                        <td>${pedido.CursoNombre}</td>
                        <td>${pedido.MenuNombre}</td>
                        <td>${pedido.FechaEntrega}</td>
                        <td>${pedido.Estado}</td>
                    </tr>`;
                tbody.innerHTML += fila;
            });

            document.getElementById('modalDetalle').style.display = 'block';
        }

        // Función para cerrar el modal
        function cerrarModal() {
            document.getElementById('modalDetalle').style.display = 'none';
        }

        // Función para descargar en CSV
        function descargarCSV() {
            let filas = document.querySelectorAll("#tablaDetalle tr");
            let csvContent = "data:text/csv;charset=utf-8,";

            filas.forEach(fila => {
                let cols = fila.querySelectorAll("td, th");
                let dataFila = [];
                cols.forEach(col => dataFila.push(col.innerText));
                csvContent += dataFila.join(",") + "\n";
            });

            let link = document.createElement("a");
            link.setAttribute("href", encodeURI(csvContent));
            link.setAttribute("download", "detalle_pedidos.csv");
            document.body.appendChild(link);
            link.click();
        }
    </script>

    <style>
        /* Estilos del modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            text-align: center;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>





    <!-- PREFERENCIAS ALIMENTICAS -->
    <h2>Preferencias Alimenticias</h2>
    <table border="1">
        <tr>
            <th>Nombre menú</th>
            <th>Fecha de entrega</th>
            <th>Colegio</th>
            <th>Curso</th>
            <th>Alumno</th>
            <th>Preferencia</th>
        </tr>
        <?php foreach ($preferencias as $preferencia) : ?>
            <tr>
                <td><?php echo htmlspecialchars($preferencia['MenuNombre']); ?></td>
                <td><?php echo htmlspecialchars($preferencia['FechaEntrega']); ?></td>
                <td><?php echo htmlspecialchars($preferencia['ColegioNombre']); ?></td>
                <td><?php echo htmlspecialchars($preferencia['CursoNombre']); ?></td>
                <td><?php echo htmlspecialchars($preferencia['AlumnoNombre']); ?></td>
                <td><?php echo htmlspecialchars($preferencia['PreferenciaNombre']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>