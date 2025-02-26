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

// -------------------- OBTENER VIANDAS POR NIVEL --------------------
$query_niveles = "
    SELECT m.Nivel_Educativo, m.Nombre AS MenuNombre, COUNT(*) AS Cantidad
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

$query_niveles .= " GROUP BY m.Nivel_Educativo, m.Nombre ORDER BY FIELD(m.Nivel_Educativo, 'Inicial', 'Primaria', 'Secundaria')";
$stmt = $pdo->prepare($query_niveles);
$stmt->execute($params_niveles);
$niveles_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -------------------- ORGANIZAR DATOS POR NIVEL --------------------
$niveles = ['Inicial' => [], 'Primaria' => [], 'Secundaria' => []];
foreach ($niveles_data as $nivel) {
    $niveles[$nivel['Nivel_Educativo']][] = $nivel;
}

// -------------------- OBTENER DETALLE DE PEDIDOS --------------------
$query_detalle = "
    SELECT pc.Id AS PedidoId, h.Nombre AS Alumno, cu.Nombre AS Curso, m.Nombre AS MenuNombre, pc.Fecha_entrega, pc.Estado, m.Nivel_Educativo
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
    WHERE pc.Estado = 'Procesando'
";
$params_detalle = [];

if (!empty($fecha_filtro)) {
    $query_detalle .= " AND pc.Fecha_entrega = ?";
    $params_detalle[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_detalle .= " AND h.Colegio_Id = ?";
    $params_detalle[] = $colegio_filtro;
}

$query_detalle .= " ORDER BY m.Nivel_Educativo, cu.Nombre";
$stmt = $pdo->prepare($query_detalle);
$stmt->execute($params_detalle);
$detalle_pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -------------------- ORGANIZAR DETALLES POR NIVEL --------------------
$detalles_por_nivel = ['Inicial' => [], 'Primaria' => [], 'Secundaria' => []];
foreach ($detalle_pedidos as $detalle) {
    $detalles_por_nivel[$detalle['Nivel_Educativo']][] = $detalle;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Cocina</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script>
        function mostrarModal(nivel) {
            document.getElementById('modal-' + nivel).style.display = 'block';
        }
        function cerrarModal(nivel) {
            document.getElementById('modal-' + nivel).style.display = 'none';
        }
        function descargarCSV(nivel) {
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Pedido ID,Alumno,Curso,Menú,Fecha de Entrega,Estado\n";

            let filas = document.querySelectorAll(`#tabla-${nivel} tbody tr`);
            filas.forEach(fila => {
                let columnas = fila.querySelectorAll("td");
                let filaTexto = [];
                columnas.forEach(columna => filaTexto.push(columna.innerText));
                csvContent += filaTexto.join(",") + "\n";
            });

            let encodedUri = encodeURI(csvContent);
            let link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "detalle_pedidos_" + nivel + ".csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</head>
<body>
    <h1>Dashboard Cocina</h1>

    <h2>Totalidad de Viandas por Nivel</h2>
    <?php foreach ($niveles as $nivel => $menus): ?>
        <h3><?php echo $nivel; ?> <button onclick="mostrarModal('<?php echo $nivel; ?>')">Ver Detalle</button></h3>
        <table border="1">
            <tr>
                <th>Menú</th>
                <th>Cantidad</th>
            </tr>
            <?php foreach ($menus as $menu): ?>
                <tr>
                    <td><?php echo htmlspecialchars($menu['MenuNombre']); ?></td>
                    <td><?php echo htmlspecialchars($menu['Cantidad']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Modal de Detalle -->
        <div id="modal-<?php echo $nivel; ?>" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
            <div style="background: white; padding: 20px; margin: 10% auto; width: 80%;">
                <h2>Detalle de <?php echo $nivel; ?></h2>
                <table id="tabla-<?php echo $nivel; ?>" border="1">
                    <tr>
                        <th>Pedido ID</th>
                        <th>Alumno</th>
                        <th>Curso</th>
                        <th>Menú</th>
                        <th>Fecha de Entrega</th>
                        <th>Estado</th>
                    </tr>
                    <?php foreach ($detalles_por_nivel[$nivel] as $detalle): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($detalle['PedidoId']); ?></td>
                            <td><?php echo htmlspecialchars($detalle['Alumno']); ?></td>
                            <td><?php echo htmlspecialchars($detalle['Curso']); ?></td>
                            <td><?php echo htmlspecialchars($detalle['MenuNombre']); ?></td>
                            <td><?php echo htmlspecialchars($detalle['Fecha_entrega']); ?></td>
                            <td><?php echo htmlspecialchars($detalle['Estado']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <button onclick="descargarCSV('<?php echo $nivel; ?>')">Descargar CSV</button>
                <button onclick="cerrarModal('<?php echo $nivel; ?>')">Cerrar</button>
            </div>
        </div>
    <?php endforeach; ?>
</body>
</html>
