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
        $stmt = $pdo->prepare("SELECT p.fecha, d.planta, d.menu, SUM(d.cantidad) as cantidad 
                               FROM Pedidos_Cuyo_Placa p 
                               JOIN Detalle_Pedidos_Cuyo_Placa d ON p.id = d.pedido_id 
                               WHERE p.fecha BETWEEN ? AND ? 
                               GROUP BY p.fecha, d.planta, d.menu
                               ORDER BY p.fecha, d.planta");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar los pedidos por fecha y planta
        foreach ($pedidos as $pedido) {
            $fecha = $pedido['fecha'];
            $planta = $pedido['planta'];
            if (!isset($pedidos_agrupados[$fecha])) {
                $pedidos_agrupados[$fecha] = [];
            }
            if (!isset($pedidos_agrupados[$fecha][$planta])) {
                $pedidos_agrupados[$fecha][$planta] = [];
            }
            $pedidos_agrupados[$fecha][$planta][] = $pedido;
            $total_viandas += $pedido['cantidad'];
        }
    } else {
        $error = "Por favor, seleccione un rango de fechas válido.";
    }
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
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-right: 10px;
            align-self: center;
        }

        input[type="date"] {
            padding: 5px;
            margin-right: 10px;
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
            margin-bottom: 20px;
        }

        .kpi {
            background-color: #28a745;
            color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            font-size: 1.2em;
            width: 200px;
        }

        .card-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            justify-content: center;
            align-items: center;
        }

        .card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            margin-top: 0;
            font-size: 1.2em;
            color: #007bff;
            text-align: center;
        }

        .card table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .card table, .card th, .card td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
        }

        .card th {
            background-color: #f8f9fa;
        }

        .card td {
            background-color: #ffffff;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
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
                <?php foreach ($plantas as $planta => $pedidos) : ?>
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
                                foreach ($pedidos as $pedido) : 
                                    $total += $pedido['cantidad'];
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pedido['menu']); ?></td>
                                        <td><?php echo htmlspecialchars($pedido['cantidad']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td><strong>Total:</strong></td>
                                    <td><strong><?php echo $total; ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php else : ?>
            <p style="text-align: center;">No hay pedidos para mostrar en el rango de fechas seleccionado.</p>
        <?php endif; ?>
    </div>
</body>
</html>
