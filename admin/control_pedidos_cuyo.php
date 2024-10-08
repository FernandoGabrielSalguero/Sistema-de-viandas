<?php
session_start();
include '../includes/header_admin.php';
include '../includes/db.php';

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: ../index.php");
    exit();
}

$fecha_inicio = '';
$fecha_fin = '';
$pedidos_agrupados = [];
$total_viandas = 0;
$totales_por_menu = [];
$pedido_ids = [];

// Manejo de actualización de cantidades
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['actualizar_cantidad'])) {
        $detalle_id = $_POST['detalle_id'];
        $nueva_cantidad = $_POST['cantidad'];

        // Actualizar la cantidad en la base de datos
        $stmt = $pdo->prepare("UPDATE Detalle_Pedidos_Cuyo_Placa SET cantidad = ? WHERE id = ?");
        $stmt->execute([$nueva_cantidad, $detalle_id]);
    } else {
        // Filtro de fechas para los pedidos
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];

        // Validar fechas
        if ($fecha_inicio && $fecha_fin && strtotime($fecha_fin) >= strtotime($fecha_inicio)) {
            $stmt = $pdo->prepare("SELECT p.id, p.fecha, d.id AS detalle_id, d.planta, d.menu, d.turno, d.cantidad 
                                   FROM Pedidos_Cuyo_Placa p 
                                   JOIN Detalle_Pedidos_Cuyo_Placa d ON p.id = d.pedido_id 
                                   WHERE p.fecha BETWEEN ? AND ? 
                                   ORDER BY p.fecha, d.planta, d.turno");
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($pedidos as $pedido) {
                $fecha = date("d/m/Y", strtotime($pedido['fecha']));
                $planta = $pedido['planta'];
                $turno = $pedido['turno'];
                $menu = $pedido['menu'];
                $cantidad = $pedido['cantidad'];
                $detalle_id = $pedido['detalle_id'];
                $pedido_id = $pedido['id'];

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
                $pedidos_agrupados[$fecha][$planta][$turno][] = $pedido;
                $total_viandas += $cantidad;

                if (!isset($totales_por_menu[$menu])) {
                    $totales_por_menu[$menu] = 0;
                }
                $totales_por_menu[$menu] += $cantidad;

                if (!in_array($pedido_id, $pedido_ids)) {
                    $pedido_ids[] = $pedido_id;
                }
            }
        } else {
            $error = "Por favor, seleccione un rango de fechas válido.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de pedidos de viandas - Admin</title>
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

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-right: 10px;
            align-self: center;
            color: #343a40;
        }

        input[type="date"] {
            padding: 8px;
            margin-right: 10px;
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

        .totales-menu {
            margin: 20px auto;
            max-width: 600px;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .totales-menu h3 {
            font-size: 1.5em;
            color: #343a40;
            text-align: center;
            margin-bottom: 15px;
        }

        .totales-menu table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .totales-menu table, .totales-menu th, .totales-menu td {
            border: 1px solid #e9ecef;
            padding: 8px;
            text-align: center;
        }

        .totales-menu th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .totales-menu td {
            background-color: #ffffff;
        }

        .totales-menu .total-final {
            text-align: right;
            font-weight: bold;
            padding-right: 15px;
            color: #28a745;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .card {
            background-color: white;
            border: none;
            border-radius: 10px;
            width: 320px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            position: relative;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            margin-top: 0;
            font-size: 1.4em;
            color: #007bff;
            text-align: center;
        }

        .card table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .card table, .card th, .card td {
            border: 1px solid #e9ecef;
            padding: 8px;
            text-align: center;
        }

        .card th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .card td {
            background-color: #ffffff;
        }

        .card .turno-title {
            text-align: left;
            font-weight: bold;
            margin-top: 15px;
            color: #343a40;
        }

        .card .pedido-id {
            font-size: 0.9em;
            color: #6c757d;
            text-align: center;
            margin-bottom: 10px;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Historial de pedidos - Admin</h1>

    <?php if (isset($error)) : ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" action="control_pedidos_cuyo.php">
        <label for="fecha_inicio">Desde:</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?php echo htmlspecialchars($fecha_inicio); ?>">
        <label for="fecha_fin">Hasta:</label>
        <input type="date" id="fecha_fin" name="fecha_fin" required value="<?php echo htmlspecialchars($fecha_fin); ?>">
        <button type="submit">Filtrar</button>
    </form>

    <div class="totales-menu">
        <h3>Totales por Menú</h3>
        <table>
            <thead>
                <tr>
                    <th>Menú</th>
                    <th>Total Pedidos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($totales_por_menu as $menu => $total): ?>
                <tr>
                    <td><?php echo htmlspecialchars($menu); ?></td>
                    <td><?php echo htmlspecialchars($total); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="total-final">Total General: <?php echo $total_viandas; ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="card-container">
        <?php if (!empty($pedidos_agrupados)) : ?>
            <?php foreach ($pedidos_agrupados as $fecha => $plantas) : ?>
                <?php foreach ($plantas as $planta => $turnos) : ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($planta); ?></h3>
                        <div class="pedido-id">N° remito digital: <?php echo implode(', ', $pedido_ids); ?></div>
                        <table>
                            <thead>
                                <tr>
                                    <th colspan="2"><?php echo htmlspecialchars($fecha); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (['Mañana', 'Tarde', 'Noche'] as $turno) : ?>
                                    <?php if (!empty($turnos[$turno])) : ?>
                                        <tr>
                                            <td colspan="2" class="turno-title"><?php echo $turno; ?></td>
                                        </tr>
                                        <?php foreach ($turnos[$turno] as $pedido) : ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($pedido['menu']); ?></td>
                                                <td>
                                                <form method="post" action="control_pedidos_cuyo.php">
                                                        <input type="hidden" name="detalle_id" value="<?php echo $pedido['detalle_id']; ?>">
                                                        <input type="number" name="cantidad" value="<?php echo $pedido['cantidad']; ?>" min="1">
                                                        <button type="submit" name="actualizar_cantidad">Actualizar</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
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
