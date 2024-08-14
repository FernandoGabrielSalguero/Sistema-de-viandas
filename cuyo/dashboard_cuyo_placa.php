<?php
session_start();
include '../includes/header_cuyo_placa.php';
include '../includes/db.php';

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Procesar formulario de filtro de fechas
$fecha_inicio = '';
$fecha_fin = '';
$pedidos_totales = [];
$totales_comida = [];
$totales_menus_globales = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Validar fechas
    if ($fecha_inicio && $fecha_fin && strtotime($fecha_fin) >= strtotime($fecha_inicio)) {
        $stmt = $pdo->prepare("SELECT pcp.Fecha, pcp.Planta_Id, p.Nombre as Planta, dpcp.Menu, SUM(dpcp.Cantidad) as CantidadTotal 
                               FROM Pedidos_Cuyo_Placa pcp 
                               JOIN Detalle_Pedidos_Cuyo_Placa dpcp ON pcp.Id = dpcp.Pedido_Id 
                               JOIN Plantas p ON pcp.Planta_Id = p.Id
                               WHERE pcp.Fecha BETWEEN ? AND ? 
                               GROUP BY pcp.Fecha, pcp.Planta_Id, dpcp.Menu");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $pedidos_totales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Inicializar totales de comida
        foreach ($pedidos_totales as $pedido) {
            $menu = $pedido['Menu'];
            if (!isset($totales_comida[$menu])) {
                $totales_comida[$menu] = 0;
            }
            $totales_comida[$menu] += $pedido['CantidadTotal'];
        }

        // Inicializar totales globales de menús
        foreach ($pedidos_totales as $pedido) {
            $menu = $pedido['Menu'];
            if (!isset($totales_menus_globales[$menu])) {
                $totales_menus_globales[$menu] = 0;
            }
            $totales_menus_globales[$menu] += $pedido['CantidadTotal'];
        }
    } else {
        $error = "Por favor, seleccione un rango de fechas válido.";
    }
}

// Función para generar el CSV
if (isset($_GET['descargar_csv'])) {
    $fecha = $_GET['fecha'];
    $planta_id = $_GET['planta_id'];

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="pedido_' . $fecha . '_' . $planta_id . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Planta', 'Fecha', 'Menu', 'Cantidad']);

    $stmt = $pdo->prepare("SELECT p.Nombre as Planta, pcp.Fecha, dpcp.Menu, dpcp.Cantidad 
                           FROM Pedidos_Cuyo_Placa pcp 
                           JOIN Detalle_Pedidos_Cuyo_Placa dpcp ON pcp.Id = dpcp.Pedido_Id 
                           JOIN Plantas p ON pcp.Planta_Id = p.Id
                           WHERE pcp.Fecha = ? AND pcp.Planta_Id = ?");
    $stmt->execute([$fecha, $planta_id]);
    $pedido_detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pedido_detalles as $detalle) {
        fputcsv($output, [$detalle['Planta'], $detalle['Fecha'], $detalle['Menu'], $detalle['Cantidad']]);
    }

    fclose($output);
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos de Viandas - Dashboard</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            width: 100%;
            margin: 0 auto 20px auto;
            text-align: center;
        }

        label {
            font-weight: bold;
        }

        input[type="date"] {
            padding: 5px;
            margin: 0 10px;
        }

        button {
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .kpi-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .kpi {
            background-color: #28a745;
            color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 200px;
            margin-bottom: 20px;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 10px;
            box-sizing: border-box;
        }

        .card h3 {
            background-color: #007bff;
            color: white;
            margin: 0 -10px 10px -10px;
            padding: 10px;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }

        .card table {
            width: 100%;
            border-collapse: collapse;
        }

        .card th, .card td {
            padding: 5px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .download-btn {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
            text-align: center;
        }

        .download-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pedidos de Viandas - Dashboard</h1>

        <form method="post" action="dashboard_cuyo_placa.php">
            <label for="fecha_inicio">Desde:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?php echo htmlspecialchars($fecha_inicio); ?>">

            <label for="fecha_fin">Hasta:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" required value="<?php echo htmlspecialchars($fecha_fin); ?>">

            <button type="submit">Filtrar</button>
        </form>

        <?php if (!empty($pedidos_totales)) : ?>
            <div class="kpi-container">
                <div class="kpi">
                    <h3>Total Viandas</h3>
                    <p><?php echo array_sum($totales_comida); ?></p>
                </div>
                <?php foreach ($totales_menus_globales as $menu => $total) : ?>
                    <div class="kpi">
                        <h3><?php echo htmlspecialchars($menu); ?></h3>
                        <p><?php echo htmlspecialchars($total); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card-container">
                <?php foreach ($pedidos_totales as $pedido) : ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($pedido['Planta']); ?></h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th><?php echo htmlspecialchars($pedido['Fecha']); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->prepare("SELECT dpcp.Menu, dpcp.Cantidad 
                                                       FROM Detalle_Pedidos_Cuyo_Placa dpcp 
                                                       JOIN Pedidos_Cuyo_Placa pcp ON dpcp.Pedido_Id = pcp.Id 
                                                       WHERE pcp.Fecha = ? AND pcp.Planta_Id = ?");
                                $stmt->execute([$pedido['Fecha'], $pedido['Planta_Id']]);
                                $detalle_pedido = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                $total_viandas = 0;
                                foreach ($detalle_pedido as $detalle) {
                                    echo "<tr><td>" . htmlspecialchars($detalle['Menu']) . "</td><td>" . htmlspecialchars($detalle['Cantidad']) . "</td></tr>";
                                    $total_viandas += $detalle['Cantidad'];
                                }
                                ?>
                                <tr>
                                    <td><strong>Total:</strong></td>
                                    <td><strong><?php echo $total_viandas; ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                        <form method="get" action="dashboard_cuyo_placa.php">
                            <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($pedido['Fecha']); ?>">
                            <input type="hidden" name="planta_id" value="<?php echo htmlspecialchars($pedido['Planta_Id']); ?>">
                            <input type="hidden" name="descargar_csv" value="1">
                            <button type="submit" class="download-btn">Descargar CSV</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
