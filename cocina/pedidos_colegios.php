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

if (!empty($fecha_filtro)) {
    $query_menus .= " AND pc.Fecha_entrega = ?";
    $params_menus[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_menus .= " AND h.Colegio_Id = ?";
    $params_menus[] = $colegio_filtro;
}

$query_menus .= " GROUP BY m.Nombre, m.Nivel_Educativo, pc.Fecha_entrega";

$params_niveles = [];

if (!empty($fecha_filtro)) {
    $query_menus .= " AND pc.Fecha_entrega = ?";
    $params_niveles[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_menus .= " AND h.Colegio_Id = ?";
    $params_niveles[] = $colegio_filtro;
}

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
$stmt = $pdo->prepare($query_menus);
$stmt->execute(array_values($params_niveles));  // Convertimos a array con valores correctos

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
        <div class="filter-item">
            <button type="submit" name="eliminar_filtro">Eliminar Filtro</button>
        </div>
    </form>

    <h2>Total de Menús</h2>
    <div class="card-container">
        <?php foreach ($menus as $menu) : ?>
            <?php
            $fechaEntrega = htmlspecialchars($menu['Fecha_entrega']);
            $menuNombre = htmlspecialchars($menu['MenuNombre']);
            $cantidad = htmlspecialchars($menu['Cantidad']);
            $prefCount = isset($preferencias_por_menu[$menuNombre]) ? count($preferencias_por_menu[$menuNombre]) : 0;
            $cardClass = $prefCount > 0 ? ($prefCount > 2 ? 'danger' : 'warning') : '';
            ?>
            <div class="card <?php echo $cardClass; ?>">
                <h3><?php echo $menuNombre; ?></h3>
                <h2><strong>Cantidad:</strong> <?php echo $cantidad; ?></h2>
                <p><strong>Fecha de entrega:</strong> <?php echo $fechaEntrega; ?></p>
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
                <td><button>Detalle</button></td>
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
</body>

</html>