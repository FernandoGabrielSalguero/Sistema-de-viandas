<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_cocina.php';
include '../includes/db.php';

// Procesar el formulario de filtro por fecha de entrega
$fecha_filtro = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filtrar_fecha'])) {
    $fecha_filtro = $_POST['fecha_entrega'];
}

// Obtener la cantidad total de viandas compradas, agrupadas por nombre de menú y día de entrega
$query_menus = "
    SELECT m.Nombre AS MenuNombre, DATE_FORMAT(pc.Fecha_entrega, '%d/%b/%y') AS FechaEntrega, COUNT(*) AS Cantidad
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

// Obtener la cantidad total de viandas compradas, divididas por colegio y cursos
$query_colegios = "
    SELECT c.Nombre AS ColegioNombre, cu.Nombre AS CursoNombre, m.Nombre AS MenuNombre, COUNT(*) AS Cantidad, DATE_FORMAT(pc.Fecha_entrega, '%d/%b/%y') AS FechaEntrega
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Colegios c ON h.Colegio_Id = c.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
";
if (!empty($fecha_filtro)) {
    $query_colegios .= " WHERE pc.Fecha_entrega = ?";
}
$query_colegios .= " GROUP BY c.Nombre, cu.Nombre, m.Nombre, pc.Fecha_entrega";
$stmt = $pdo->prepare($query_colegios);
if (!empty($fecha_filtro)) {
    $stmt->execute([$fecha_filtro]);
} else {
    $stmt->execute();
}
$colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener los alumnos con preferencias alimenticias
$query_preferencias = "
    SELECT DATE_FORMAT(pc.Fecha_entrega, '%d/%b/%y') AS FechaEntrega, c.Nombre AS ColegioNombre, cu.Nombre AS CursoNombre, h.Nombre AS AlumnoNombre, m.Nombre AS MenuNombre, p.Nombre AS PreferenciaNombre
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Colegios c ON h.Colegio_Id = c.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
    JOIN Preferencias_Alimenticias p ON h.Preferencias_Alimenticias = p.Id
    WHERE h.Preferencias_Alimenticias IS NOT NULL
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
    </style>
</head>
<body>
    <h1>Dashboard Cocina</h1>
    
    <form method="post" action="dashboard.php">
        <label for="fecha_entrega">Filtrar por Fecha de Entrega:</label>
        <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($fecha_filtro); ?>">
        <button type="submit" name="filtrar_fecha">Filtrar</button>
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
    </div>

    <h2>Totalidad de Viandas por Colegio y Curso</h2>
    <div class="kpi-container">
        <?php foreach ($colegios as $colegio) : ?>
            <div class="kpi">
                <h3><?php echo htmlspecialchars($colegio['ColegioNombre']); ?></h3>
                <h4><?php echo htmlspecialchars($colegio['CursoNombre']); ?></h4>
                <p>Menú: <?php echo htmlspecialchars($colegio['MenuNombre']); ?></p>
                <p>Cantidad: <?php echo htmlspecialchars($colegio['Cantidad']); ?></p>
                <p>Fecha de entrega: <?php echo htmlspecialchars($colegio['FechaEntrega']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>Preferencias Alimenticias</h2>
    <table border="1">
        <tr>
            <th>Fecha de entrega</th>
            <th>Colegio</th>
            <th>Curso</th>
            <th>Alumno</th>
            <th>Menú elegido</th>
            <th>Preferencia</th>
        </tr>
        <?php foreach ($preferencias as $preferencia) : ?>
            <tr>
                <td><?php echo htmlspecialchars($preferencia['FechaEntrega']); ?></td>
                <td><?php echo htmlspecialchars($preferencia['ColegioNombre']); ?></td>
                <td><?php echo htmlspecialchars($preferencia['CursoNombre']); ?></td>
                <td><?php echo htmlspecialchars($preferencia['AlumnoNombre']); ?></td>
                <td><?php echo htmlspecialchars($preferencia['MenuNombre']); ?></td>
                <td><?php echo htmlspecialchars($preferencia['PreferenciaNombre']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
