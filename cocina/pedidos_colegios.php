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

$query_menus .= " GROUP BY m.Nombre, m.Nivel_Educativo, pc.Fecha_entrega"; // SOLO UN GROUP BY
$stmt = $pdo->prepare($query_menus);
$stmt->execute($params_menus);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    .tabla-niveles th, .tabla-niveles td {
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
