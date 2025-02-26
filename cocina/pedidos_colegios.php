<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_cocina.php';
include '../includes/db.php';

// Inicializar filtros
$fecha_filtro = isset($_GET['fecha_entrega']) ? $_GET['fecha_entrega'] : '';
$colegio_filtro = isset($_GET['colegio']) ? $_GET['colegio'] : '';

// Consulta para obtener menús agrupados con cantidad total
$query_menus = "
    SELECT m.Nombre AS MenuNombre, DATE_FORMAT(pc.Fecha_entrega, '%d/%m/%y') AS FechaEntrega, COUNT(*) AS Cantidad
    FROM Pedidos_Comida pc
    JOIN Menú m ON pc.Menú_Id = m.Id
    WHERE pc.Estado = 'Procesando' ";

$params = [];
if (!empty($fecha_filtro)) {
    $query_menus .= " AND pc.Fecha_entrega = ? ";
    $params[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_menus .= " AND pc.Colegio_Id = ? ";
    $params[] = $colegio_filtro;
}
$query_menus .= " GROUP BY m.Nombre, pc.Fecha_entrega";

$stmt = $pdo->prepare($query_menus);
$stmt->execute($params);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Cocina</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Dashboard Cocina</h1>

    <!-- Formulario de Filtros -->
    <form method="get" action="dashboard.php" class="filter-container">
        <div class="filter-item">
            <label for="fecha_entrega">Filtrar por Fecha de Entrega:</label>
            <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($fecha_filtro); ?>">
        </div>
        <div class="filter-item">
            <label for="colegio">Filtrar por Colegio:</label>
            <input type="text" id="colegio" name="colegio" value="<?php echo htmlspecialchars($colegio_filtro); ?>">
        </div>
        <div class="filter-item">
            <button type="submit">Filtrar</button>
        </div>
        <div class="filter-item">
            <a href="dashboard.php" class="btn-reset">Eliminar Filtro</a>
        </div>
    </form>

    <!-- Tarjetas de Total de Menús -->
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
</body>
</html>
