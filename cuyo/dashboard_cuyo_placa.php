<?php
session_start();
include '../includes/header_cuyo_placa.php';
include '../includes/db.php';

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cuyo_placa') {
    header("Location: ../index.php");
    exit();
}

$fecha_inicio = '';
$fecha_fin = '';
$pedidos_agrupados = [];
$total_viandas = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Validar fechas
    if ($fecha_inicio && $fecha_fin && strtotime($fecha_fin) >= strtotime($fecha_inicio)) {
        // Obtener los pedidos y detalles de la base de datos
        $stmt = $pdo->prepare("SELECT p.fecha, d.planta, d.menu, d.periodo, SUM(d.cantidad) as cantidad 
                               FROM Pedidos_Cuyo_Placa p 
                               JOIN Detalle_Pedidos_Cuyo_Placa d ON p.id = d.pedido_id 
                               WHERE p.fecha BETWEEN ? AND ? 
                               GROUP BY p.fecha, d.planta, d.menu, d.periodo
                               ORDER BY p.fecha, d.planta, d.periodo");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar los pedidos por fecha, planta, y periodo
        foreach ($pedidos as $pedido) {
            $fecha = $pedido['fecha'];
            $planta = $pedido['planta'];
            $periodo = $pedido['periodo'];
            if (!isset($pedidos_agrupados[$fecha])) {
                $pedidos_agrupados[$fecha] = [];
            }
            if (!isset($pedidos_agrupados[$fecha][$planta])) {
                $pedidos_agrupados[$fecha][$planta] = [
                    'Mañana' => [],
                    'Tarde' => [],
                    'Noche' => [],
                ];
            }
            $pedidos_agrupados[$fecha][$planta][$periodo][] = $pedido;
            $total_viandas += $pedido['cantidad'];
        }
    } else {
        $error = "Por favor, seleccione un rango de fechas válido.";
    }
}

// Función para generar el archivo CSV
function generarCSV($fecha, $planta, $pedidos) {
    $filename = "pedido_{$planta}_{$fecha}.csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=' . $filename);

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Fecha', 'Planta', 'Menú', 'Periodo', 'Cantidad']);
    
    foreach ($pedidos as $periodo => $detalle_pedidos) {
        foreach ($detalle_pedidos as $pedido) {
            fputcsv($output, [$pedido['fecha'], $pedido['planta'], $pedido['menu'], $periodo, $pedido['cantidad']]);
        }
    }
    fclose($output);
    exit;
}

// Manejo de la descarga de CSV
if (isset($_GET['descargar']) && $_GET['descargar'] == 'csv' && isset($_GET['fecha']) && isset($_GET['planta'])) {
    $fecha = $_GET['fecha'];
    $planta = $_GET['planta'];
    if (isset($pedidos_agrupados[$fecha][$planta])) {
        generarCSV($fecha, $planta, $pedidos_agrupados[$fecha][$planta]);
    }
}

// Función para generar imagenes JPG de las tarjetas
function generarJPG($fecha, $planta, $pedidos) {
    // Código para generar y descargar la imagen JPG
    // Nota: Este es un proceso avanzado que requeriría bibliotecas adicionales como GD o Imagick en PHP
    // Aquí solo se esboza el proceso
    // header('Content-Type: image/jpeg');
    // Código para dibujar y renderizar la imagen basada en $pedidos
    // ...
    exit;
}

// Manejo de la descarga de JPG
if (isset($_GET['descargar']) && $_GET['descargar'] == 'jpg' && isset($_GET['fecha']) && isset($_GET['planta'])) {
    $fecha = $_GET['fecha'];
    $planta = $_GET['planta'];
    if (isset($pedidos_agrupados[$fecha][$planta])) {
        generarJPG($fecha, $planta, $pedidos_agrupados[$fecha][$planta]);
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos de Viandas - Dashboard</title>
    <style>
        /* Estilos existentes */
        /* (No se repiten aquí por brevedad, asume que los estilos originales se mantienen) */
    </style>
</head>
<body>
    <h1>Pedidos de Viandas - Dashboard</h1>

    <?php if (isset($error)) : ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" action="dashboard_cuyo_placa.php">
        <label for="fecha_inicio">Desde:</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?php echo htmlspecialchars($fecha_inicio); ?>">
        <label for="fecha_fin">Hasta:</label>
        <input type="date" id="fecha_fin" name="fecha_fin" required value="<?php echo htmlspecialchars($fecha_fin); ?>">
        <button type="submit">Filtrar</button>
    </form>

    <div class="kpi-container">
        <div class="kpi">
            <p>Total Viandas</p>
            <p><?php echo $total_viandas; ?></p>
        </div>
    </div>

    <div class="card-container">
        <?php if (!empty($pedidos_agrupados)) : ?>
            <?php foreach ($pedidos_agrupados as $fecha => $plantas) : ?>
                <?php foreach ($plantas as $planta => $pedidos_por_periodo) : ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($planta); ?></h3>
                        <table>
                            <thead>
                                <tr>
                                    <th colspan="2"><?php echo htmlspecialchars($fecha); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                foreach (['Mañana', 'Tarde', 'Noche'] as $periodo) : 
                                    if (!empty($pedidos_por_periodo[$periodo])) :
                                ?>
                                    <tr>
                                        <td colspan="2"><strong><?php echo $periodo; ?></strong></td>
                                    </tr>
                                    <?php foreach ($pedidos_por_periodo[$periodo] as $pedido) : 
                                        $total += $pedido['cantidad'];
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pedido['menu']); ?></td>
                                            <td><?php echo htmlspecialchars($pedido['cantidad']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php 
                                    endif; 
                                endforeach; 
                                ?>
                                <tr>
                                    <td><strong>Total:</strong></td>
                                    <td><strong><?php echo $total; ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                        <button onclick="window.location.href='dashboard_cuyo_placa.php?descargar=csv&fecha=<?php echo urlencode($fecha); ?>&planta=<?php echo urlencode($planta); ?>'">Descargar CSV</button>
                        <button onclick="window.location.href='dashboard_cuyo_placa.php?descargar=jpg&fecha=<?php echo urlencode($fecha); ?>&planta=<?php echo urlencode($planta); ?>'">Descargar JPG</button>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php else : ?>
            <p style="text-align: center;">No hay pedidos para mostrar en el rango de fechas seleccionado.</p>
        <?php endif; ?>
    </div>
</body>
</html>
