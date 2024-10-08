<?php
ob_start(); // Inicia el buffer de salida

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

$fecha_inicio = '';
$fecha_fin = '';
$pedidos_totales = [];
$kpis = [];
$tabla_pedidos = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Validar fechas
    if ($fecha_inicio && $fecha_fin && strtotime($fecha_fin) >= strtotime($fecha_inicio)) {
        $stmt = $pdo->prepare("SELECT p.fecha, p.created_at, d.pedido_id, d.planta, d.turno, d.menu, d.cantidad 
                               FROM Pedidos_Cuyo_Placa p
                               JOIN Detalle_Pedidos_Cuyo_Placa d ON p.id = d.pedido_id
                               WHERE p.fecha BETWEEN ? AND ?
                               ORDER BY p.fecha, d.planta, d.turno");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $pedidos_totales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcular KPIs y organizar datos para la tabla
        foreach ($pedidos_totales as $pedido) {
            $menu = $pedido['menu'];
            if (!isset($kpis[$menu])) {
                $kpis[$menu] = 0;
            }
            $kpis[$menu] += $pedido['cantidad'];
            $tabla_pedidos[] = $pedido;
        }
    } else {
        $error = "Por favor, seleccione un rango de fechas válido.";
    }
}

// Función para generar archivo CSV
function generarCSV($tabla_pedidos) {
    ob_end_clean(); // Limpia el buffer antes de generar el CSV

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="pedidos_cuyo_placa.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Encabezados
    fputcsv($output, ['Fecha', 'Created At', 'Pedido ID', 'Planta', 'Turno', 'Menu', 'Cantidad']);
    
    // Datos
    foreach ($tabla_pedidos as $pedido) {
        fputcsv($output, [
            $pedido['fecha'], 
            $pedido['created_at'], 
            $pedido['pedido_id'], 
            $pedido['planta'], 
            $pedido['turno'], 
            $pedido['menu'], 
            $pedido['cantidad']
        ]);
    }
    
    fclose($output);
    exit;
}

// Manejar la descarga del archivo CSV
if (isset($_POST['descargar_excel'])) {
    generarCSV($tabla_pedidos);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Viandas - Administrador</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
            color: #343a40;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            gap: 10px;
        }

        label {
            font-weight: bold;
            align-self: center;
            color: #343a40;
        }

        input[type="date"] {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            font-size: 1em;
        }

        button {
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .kpi-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-bottom: 20px;
        }

        .kpi {
            background-color: #007bff;
            color: white;
            padding: 20px;
            margin: 10px;
            border-radius: 5px;
            text-align: center;
            flex: 1;
            min-width: 150px;
            font-size: 1.2em;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            border: 1px solid #e9ecef;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f8f9fa;
            color: #343a40;
            font-weight: bold;
        }

        td {
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reporte de Viandas - Administrador</h1>

        <?php if (isset($error)) : ?>
            <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label for="fecha_inicio">Desde:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?php echo htmlspecialchars($fecha_inicio); ?>">

            <label for="fecha_fin">Hasta:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" required value="<?php echo htmlspecialchars($fecha_fin); ?>">

            <button type="submit">Filtrar</button>
            <button type="submit" name="descargar_excel">Descargar Excel</button>
        </form>

        <!-- Mostrar KPIs -->
        <div class="kpi-container">
            <?php foreach ($kpis as $menu => $total) : ?>
                <div class="kpi">
                    <?php echo htmlspecialchars($menu); ?>
                    <p><?php echo htmlspecialchars($total); ?> viandas</p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Mostrar la tabla -->
        <table>
            <thead>
                <tr>
                    <th>Fecha de entrega</th>
                    <th>Fecha de pedidos</th>
                    <th>Remito N°</th>
                    <th>Planta</th>
                    <th>Turno</th>
                    <th>Servicio</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tabla_pedidos as $pedido) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pedido['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['pedido_id']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['planta']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['turno']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['menu']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['cantidad']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
