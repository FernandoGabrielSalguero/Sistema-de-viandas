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
    SELECT m.Nombre AS MenuNombre, COUNT(*) AS Cantidad, pc.Fecha_entrega 
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

$query_menus .= " GROUP BY m.Nombre, pc.Fecha_entrega";
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

$params_preferencias = []; 

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

$preferencias_por_menu = [];
foreach ($preferencias as $pref) {
    $menu = $pref['MenuNombre'];
    if (!isset($preferencias_por_menu[$menu])) {
        $preferencias_por_menu[$menu] = [];
    }
    $preferencias_por_menu[$menu][] = $pref;
}

// -------------------- OBTENER VIANDAS POR NIVEL --------------------
$query_viandas_nivel = "
    SELECT n.Nombre AS Nivel, COUNT(*) AS Cantidad
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Niveles n ON h.Nivel_Id = n.Id
    WHERE pc.Estado = 'Procesando'
";

$params_viandas_nivel = [];

if (!empty($fecha_filtro)) {
    $query_viandas_nivel .= " AND pc.Fecha_entrega = ?";
    $params_viandas_nivel[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_viandas_nivel .= " AND h.Colegio_Id = ?";
    $params_viandas_nivel[] = $colegio_filtro;
}

$query_viandas_nivel .= " GROUP BY n.Nombre";
$stmt = $pdo->prepare($query_viandas_nivel);
$stmt->execute($params_viandas_nivel);
$viandas_por_nivel = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        .table-container {
            margin-top: 20px;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        table {
            width: 60%;
            border-collapse: collapse;
            text-align: center;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
        }
        th {
            background-color: #f4f4f4;
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
            <div class="card">
                <h3><?php echo htmlspecialchars($menu['MenuNombre']); ?></h3>
                <h2><strong>Cantidad:</strong> <?php echo htmlspecialchars($menu['Cantidad']); ?></h2>
                <p><strong>Fecha de entrega:</strong> <?php echo htmlspecialchars($menu['Fecha_entrega']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>Viandas por Nivel</h2>
    <div class="table-container">
        <table>
            <tr>
                <th>Nivel</th>
                <th>Cantidad de Viandas</th>
            </tr>
            <?php foreach ($viandas_por_nivel as $nivel) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($nivel['Nivel']); ?></td>
                    <td><?php echo htmlspecialchars($nivel['Cantidad']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
