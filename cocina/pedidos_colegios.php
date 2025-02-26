<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_cocina.php';
include '../includes/db.php';

// Variables de filtro
$fecha_filtro = isset($_GET['fecha_entrega']) ? $_GET['fecha_entrega'] : '';
$colegio_filtro = isset($_GET['colegio']) ? $_GET['colegio'] : '';

// -------------------- OBTENER MENÚS --------------------
$query_menus = "
    SELECT m.Nombre AS MenuNombre, m.Nivel_Educativo, COUNT(*) AS Cantidad, pc.Fecha_entrega 
    FROM Pedidos_Comida pc
    JOIN Menú m ON pc.Menú_Id = m.Id
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    WHERE pc.Estado = 'Procesando'
";

$params_menus = [];

// ✅ Agregar filtros SOLO UNA VEZ
if (!empty($fecha_filtro)) {
    $query_menus .= " AND pc.Fecha_entrega = ?";
    $params_menus[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_menus .= " AND h.Colegio_Id = ?";
    $params_menus[] = $colegio_filtro;
}

// ✅ AGRUPAR correctamente
$query_menus .= " GROUP BY m.Nombre, m.Nivel_Educativo, pc.Fecha_entrega";

// ✅ Preparar y ejecutar consulta
$stmt = $pdo->prepare($query_menus);
$stmt->execute($params_menus);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);


// -------------------- OBTENER PREFERENCIAS ALIMENTICIAS --------------------
$query_preferencias = "
    SELECT h.Nombre AS Alumno, cu.Nombre AS Curso, m.Nombre AS MenuNombre, pa.Nombre AS Preferencia
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
    JOIN Preferencias_Alimenticias pa ON pc.Preferencias_alimenticias = pa.Id
    WHERE pc.Estado = 'Procesando' AND pa.Nombre != 'Sin preferencias'
";

$params_preferencias = []; // Nuevo array de parámetros

if (!empty($fecha_filtro)) {
    $query_preferencias .= " AND pc.Fecha_entrega = ?";
    $params_preferencias[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_preferencias .= " AND h.Colegio_Id = ?";
    $params_preferencias[] = $colegio_filtro;
}

$stmt = $pdo->prepare($query_preferencias);
$stmt->execute($params_preferencias);
$preferencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar preferencias por menú
$preferencias_por_menu = [];
foreach ($preferencias as $pref) {
    $menu = $pref['MenuNombre'];
    if (!isset($preferencias_por_menu[$menu])) {
        $preferencias_por_menu[$menu] = [];
    }
    $preferencias_por_menu[$menu][] = $pref;
}

$query_menus = "
    SELECT m.Nombre AS MenuNombre, m.Nivel_Educativo, COUNT(*) AS Cantidad, pc.Fecha_entrega 
    FROM Pedidos_Comida pc
    JOIN Menú m ON pc.Menú_Id = m.Id
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    WHERE pc.Estado = 'Procesando'
";
$params_niveles = [];

if (!empty($fecha_filtro)) {
    $query_menus .= " AND pc.Fecha_entrega = ?";
    $params_niveles[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_menus .= " AND h.Colegio_Id = ?";
    $params_niveles[] = $colegio_filtro;
}

$query_menus .= " GROUP BY m.Nombre, m.Nivel_Educativo, pc.Fecha_entrega";
$stmt->execute(array_values($params_niveles));

// Organizar datos para la tabla
$niveles = ['Inicial', 'Primaria', 'Secundaria'];
$menues = [];
$data_niveles = [];

foreach ($menus as $menu) {
    $nivel = $menu['Nivel_Educativo'];
    $nombre_menu = $menu['MenuNombre'];
    $cantidad = $menu['Cantidad'];

    if (!isset($menues[$nombre_menu])) {
        $menues[$nombre_menu] = [];
    }

    $menues[$nombre_menu][$nivel] = $cantidad;

    if (!isset($data_niveles[$nivel])) {
        $data_niveles[$nivel] = [];
    }

    $data_niveles[$nivel][$nombre_menu] = $cantidad;
}

// Calcular totales
$totales_menus = [];
$total_general = 0;

foreach ($menues as $menu => $niveles_data) {
    $total_menu = array_sum($niveles_data);
    $totales_menus[$menu] = $total_menu;
    $total_general += $total_menu;
}

// -------------------- OBTENER TOTALES ACUMULADOS DE CADA MENÚ --------------------
$query_menus_totales = "
    SELECT m.Nombre AS MenuNombre, COUNT(*) AS Cantidad
    FROM Pedidos_Comida pc
    JOIN Menú m ON pc.Menú_Id = m.Id
    WHERE pc.Estado = 'Procesando'
";

$params_menus_totales = [];

if (!empty($fecha_filtro)) {
    $query_menus_totales .= " AND pc.Fecha_entrega = ?";
    $params_menus_totales[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_menus_totales .= " AND pc.Colegio_Id = ?";
    $params_menus_totales[] = $colegio_filtro;
}

// ✅ Agrupar SOLO por nombre del menú para obtener el total general
$query_menus_totales .= " GROUP BY m.Nombre";

$stmt = $pdo->prepare($query_menus_totales);
$stmt->execute($params_menus_totales);
$menus_totales = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Cocina</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .card {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            width: 600px;
            text-align: left;
            background-color: #f8f8f8;
        }

        .warning {
            background-color: #ffeb3b;
        }

        .danger {
            background-color: #f44336;
            color: white;
        }

        .card h3 {
            margin-bottom: 10px;
        }

        .card ul {
            list-style: none;
            padding: 0;
        }

        .card ul li {
            margin-bottom: 5px;
        }

        .card p {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <h1>Dashboard Cocina</h1>

    <form method="get" action="pedidos_colegios.php" class="filter-container">
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
    </form>

    <h2>Total de Menús</h2>
    <div class="card-container">
        <?php foreach ($menus_totales as $menu) : ?>
            <?php
            $menuNombre = htmlspecialchars($menu['MenuNombre']);
            $cantidad = htmlspecialchars($menu['Cantidad']);
            $prefCount = isset($preferencias_por_menu[$menuNombre]) ? count($preferencias_por_menu[$menuNombre]) : 0;
            $cardClass = $prefCount > 0 ? ($prefCount > 2 ? 'danger' : 'warning') : '';
            ?>
            <div class="card <?php echo $cardClass; ?>">
                <h3><?php echo $menuNombre; ?></h3>
                <h2><strong>Cantidad total:</strong> <?php echo $cantidad; ?></h2>
                <?php if ($prefCount > 0) : ?>
                    <p><strong>⚠ <?php echo $prefCount; ?> alumno(s) con preferencias alimenticias</strong></p>
                    <ul>
                        <?php foreach ($preferencias_por_menu[$menuNombre] as $pref) : ?>
                            <li><strong>Alumno:</strong> <?php echo htmlspecialchars($pref['Alumno']); ?></li>
                            <li><strong>Curso:</strong> <?php echo htmlspecialchars($pref['Curso']); ?></li>
                            <li><strong>Preferencia:</strong> <?php echo htmlspecialchars($pref['Preferencia']); ?></li>
                            <hr>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>


    <!-- TABLA DE TOTALIDAD DE VIANDAS POR NIVEL -->
    <h2>Totalidad de Viandas por Nivel</h2>
    <table border="1" class="tabla-niveles">
        <tr>
            <th>Nivel</th>
            <?php foreach ($menues as $menu => $val) : ?>
                <th><?php echo htmlspecialchars($menu); ?></th>
            <?php endforeach; ?>
            <th>Total</th>
            <th>Detalle</th>
        </tr>
        <?php foreach ($niveles as $nivel) : ?>
            <tr>
                <td><?php echo $nivel; ?></td>
                <?php foreach ($menues as $menu => $val) : ?>
                    <td><?php echo isset($data_niveles[$nivel][$menu]) ? $data_niveles[$nivel][$menu] : 0; ?></td>
                <?php endforeach; ?>
                <td><strong><?php echo array_sum($data_niveles[$nivel] ?? []); ?></strong></td>
                <td><button onclick="cargarDetalle('<?php echo $nivel; ?>')">Detalle</button></td>
            </tr>
        <?php endforeach; ?>
        <tr style="background-color: #d0e7ff;">
            <td><strong>Total</strong></td>
            <?php foreach ($totales_menus as $total) : ?>
                <td><strong><?php echo $total; ?></strong></td>
            <?php endforeach; ?>
            <td><strong><?php echo $total_general; ?></strong></td>
            <td></td>
        </tr>
    </table>

    <style>
        .tabla-niveles {
            width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .tabla-niveles th,
        .tabla-niveles td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .tabla-niveles th {
            background-color: #007BFF;
            color: white;
        }

        .tabla-niveles tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .tabla-niveles tr:hover {
            background-color: #ddd;
        }

        .tabla-niveles button {
            padding: 5px 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }

        .tabla-niveles button:hover {
            background-color: #0056b3;
        }
    </style>

    <!-- MODAL PARA DETALLES -->
    <div id="detalleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2>Detalles de Viandas - <span id="modalNivel"></span></h2>
            <table border="1" class="tabla-detalles">
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Hijo</th>
                        <th>Curso</th>
                        <th>Menú</th>
                        <th>Fecha de Entrega</th>
                        <th>Estado</th>
                        <th>Preferencias Alimenticias</th>
                    </tr>
                </thead>
                <tbody id="detalleContenido"></tbody>
            </table>
        </div>
    </div>

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
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            text-align: center;
            box-shadow: 0px 0px 10px #00000050;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .tabla-detalles {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .tabla-detalles th,
        .tabla-detalles td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .tabla-detalles th {
            background-color: #007BFF;
            color: white;
        }

        .tabla-detalles tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .tabla-detalles tr:hover {
            background-color: #ddd;
        }
    </style>

    <script>
        function cargarDetalle(nivel) {
            // Simulación de datos traídos desde PHP con AJAX
            fetch(`obtener_detalles.php?nivel=${nivel}`)
                .then(response => response.json())
                .then(data => {
                    let contenido = '';

                    if (data.length === 0) {
                        contenido = '<tr><td colspan="7">No hay datos disponibles para este nivel.</td></tr>';
                    } else {
                        data.forEach(pedido => {
                            contenido += `
                            <tr>
                                <td>${pedido.id_pedido}</td>
                                <td>${pedido.hijo}</td>
                                <td>${pedido.curso}</td>
                                <td>${pedido.menu}</td>
                                <td>${pedido.fecha_entrega}</td>
                                <td>${pedido.estado}</td>
                                <td>${pedido.preferencias_alimenticias}</td>
                            </tr>
                        `;
                        });
                    }

                    document.getElementById("detalleContenido").innerHTML = contenido;
                    document.getElementById("modalNivel").innerText = nivel;
                    document.getElementById("detalleModal").style.display = "block";
                })
                .catch(error => console.error("Error cargando los datos:", error));
        }

        function cerrarModal() {
            document.getElementById("detalleModal").style.display = "none";
        }
    </script>
</body>

</html>