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
";
if (!empty($fecha_filtro) || !empty($colegio_filtro)) {
    $query_menus .= " WHERE ";
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
";
if (!empty($fecha_filtro) || !empty($colegio_filtro)) {
    $query_colegios .= " WHERE ";
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
    WHERE pc.Preferencias_alimenticias IS NOT NULL
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
    'Nivel Inicial' => ['Nivel Inicial Sala 3A', 'Nivel Inicial Sala 3B', 'Nivel Inicial Sala 4A', 'Nivel Inicial Sala 4B', 'Nivel Inicial Sala 5A', 'Nivel Inicial Sala 5B'],
    'Primaria' => ['Primaria Primer Grado A', 'Primaria Primer Grado B', 'Primaria Segundo Grado', 'Primaria Tercer Grado', 'Primaria cuarto Grado', 'Primaria Quinto Grado', 'Primaria Sexto Grado', 'Primaria Septimo Grado'],
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

    <h2>Totalidad de Viandas por Colegio y Nivel</h2>
    <?php foreach ($niveles_data as $nivel => $menus) : ?>
        <h3><?php echo htmlspecialchars($nivel); ?></h3>
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
