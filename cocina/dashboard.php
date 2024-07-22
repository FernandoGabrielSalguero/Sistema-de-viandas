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
if (!empty($fecha_filtro)) {
    $query_menus .= " WHERE pc.Fecha_entrega = ?";
}
$query_menus .= " GROUP BY m.Nombre, pc.Fecha_entrega";
$stmt = $pdo->prepare($query_menus);
if (!empty($fecha_filtro)) {
    $stmt->execute([$fecha_filtro]);
} else {
    $stmt->execute();
}
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular el total de viandas pedidas
$total_viandas = array_sum(array_column($menus, 'Cantidad'));

// Obtener la cantidad total de viandas compradas, divididas por colegio y cursos
$query_colegios = "
    SELECT c.Nombre AS ColegioNombre, cu.Nombre AS CursoNombre, m.Nombre AS MenuNombre, COUNT(*) AS Cantidad, DATE_FORMAT(pc.Fecha_entrega, '%d/%m/%y') AS FechaEntrega
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Colegios c ON h.Colegio_Id = c.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
";
if (!empty($fecha_filtro) && !empty($colegio_filtro)) {
    $query_colegios .= " WHERE pc.Fecha_entrega = ? AND c.Id = ?";
} elseif (!empty($fecha_filtro)) {
    $query_colegios .= " WHERE pc.Fecha_entrega = ?";
} elseif (!empty($colegio_filtro)) {
    $query_colegios .= " WHERE c.Id = ?";
}
$query_colegios .= " GROUP BY c.Nombre, cu.Nombre, m.Nombre, pc.Fecha_entrega";
$stmt = $pdo->prepare($query_colegios);
if (!empty($fecha_filtro) && !empty($colegio_filtro)) {
    $stmt->execute([$fecha_filtro, $colegio_filtro]);
} elseif (!empty($fecha_filtro)) {
    $stmt->execute([$fecha_filtro]);
} elseif (!empty($colegio_filtro)) {
    $stmt->execute([$colegio_filtro]);
} else {
    $stmt->execute();
}
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
";
if (!empty($fecha_filtro)) {
    $query_preferencias .= " AND pc.Fecha_entrega = ?";
}
$stmt = $pdo->prepare($query_preferencias);
if (!empty($fecha_filtro)) {
    $stmt->execute([$fecha_filtro]);
} else {
    $stmt->execute();
}
$preferencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar los resultados por nivel y curso
$niveles = [
    'Nivel Inicial' => ['Nivel Inicial Sala 3A', 'Nivel Inicial Sala 3B', 'Nivel Inicial Sala 4A', 'Nivel Inicial Sala 4B', 'Nivel Inicial Sala 5A', 'Nivel Inicial Sala 5B'],
    'Primaria' => ['Primaria Primer Grado A', 'Primaria Primer Grado B', 'Primaria Segundo Grado', 'Primaria Tercer Grado', 'Primaria cuarto Grado', 'Primaria Quinto Grado', 'Primaria Sexto Grado', 'Primaria Septimo Grado'],
    'Secundaria' => ['Secundaria Primer Año', 'Secundaria Segundo Año', 'Secundaria Tercer año']
];

$niveles_data = [];
foreach ($niveles as $nivel => $cursos) {
    foreach ($cursos as $curso) {
        foreach ($colegios as $colegio) {
            if ($colegio['CursoNombre'] == $curso) {
                $niveles_data[$nivel][$curso][] = $colegio;
            }
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Dashboard Cocina</h1>
    
    <form method="post" action="dashboard.php" class="filter-container">
        <div class="filter-item">
            <label for="fecha_entrega">Filtrar por Fecha de Entrega:</label>
            <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($fecha_filtro); ?>">
        </div>
        <div class="filter-item">
            <label for="colegio">Filtrar por Colegio:</label>
            <select id="colegio" name="colegio">
                <option value="">Seleccione un colegio</option>
                <?php
                $colegios_query = $pdo->query("SELECT Id, Nombre FROM Colegios");
                while ($colegio = $colegios_query->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $colegio['Id'] . '"' . ($colegio_filtro == $colegio['Id'] ? ' selected' : '') . '>' . htmlspecialchars($colegio['Nombre']) . '</option>';
                }
                ?>
            </select>
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
        <?php foreach ($menus as $menu) : ?>
            <div class="kpi">
                <h3><?php echo htmlspecialchars($menu['MenuNombre']); ?></h3>
                <p>Cantidad: <?php echo htmlspecialchars($menu['Cantidad']); ?></p>
                <p>Fecha de entrega: <?php echo htmlspecialchars($menu['FechaEntrega']); ?></p>
            </div>
        <?php endforeach; ?>
        <div class="kpi">
            <h3>Total Viandas</h3>
            <p><?php echo $total_viandas; ?></p>
        </div>
    </div>

    <h2>Totalidad de Viandas por Colegio y Curso</h2>
    <?php foreach ($niveles_data as $nivel => $cursos) : ?>
        <h2><?php echo htmlspecialchars($nivel); ?></h2>
        <?php foreach ($cursos as $curso_nombre => $datos) : ?>
            <h3><?php echo htmlspecialchars($curso_nombre); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Colegio</th>
                        <th>Menú</th>
                        <th>Cantidad</th>
                        <th>Fecha de entrega</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datos as $dato) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dato['ColegioNombre']); ?></td>
                            <td><?php echo htmlspecialchars($dato['MenuNombre']); ?></td>
                            <td><?php echo htmlspecialchars($dato['Cantidad']); ?></td>
                            <td><?php echo htmlspecialchars($dato['FechaEntrega']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
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
