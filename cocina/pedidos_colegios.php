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

// -------------------- OBTENER TOTAL DE VIANDAS POR NIVEL --------------------
$query_niveles = "
    SELECT m.Nivel_Educativo, COUNT(*) AS Cantidad
    FROM Pedidos_Comida pc
    JOIN Menú m ON pc.Menú_Id = m.Id
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    WHERE pc.Estado = 'Procesando'
";
$params_niveles = [];

if (!empty($fecha_filtro)) {
    $query_niveles .= " AND pc.Fecha_entrega = ?";
    $params_niveles[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_niveles .= " AND h.Colegio_Id = ?";
    $params_niveles[] = $colegio_filtro;
}

$query_niveles .= " GROUP BY m.Nivel_Educativo ORDER BY FIELD(m.Nivel_Educativo, 'Inicial', 'Primaria', 'Secundaria')";
$stmt = $pdo->prepare($query_niveles);
$stmt->execute($params_niveles);
$niveles_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        .tabla-niveles {
            margin-top: 30px;
            width: 50%;
            border-collapse: collapse;
        }
        .tabla-niveles th, .tabla-niveles td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .tabla-niveles th {
            background-color: #007BFF;
            color: white;
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
            ?>
            <div class="card">
                <h3><?php echo $menuNombre; ?></h3>
                <h2><strong>Cantidad:</strong> <?php echo $cantidad; ?></h2>
                <p><strong>Fecha de entrega:</strong> <?php echo $fechaEntrega; ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabla de viandas por nivel -->
    <h2>Totalidad de Viandas por Nivel</h2>
    <table class="tabla-niveles">
        <tr>
            <th>Nivel Educativo</th>
            <th>Total de Viandas</th>
        </tr>
        <?php foreach ($niveles_data as $nivel) : ?>
            <tr>
                <td><?php echo htmlspecialchars($nivel['Nivel_Educativo']); ?></td>
                <td><?php echo htmlspecialchars($nivel['Cantidad']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
