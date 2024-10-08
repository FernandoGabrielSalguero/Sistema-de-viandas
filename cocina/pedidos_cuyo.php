<?php
session_start();
include '../includes/header_cocina.php';
include '../includes/db.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cocina') {
    header("Location: ../index.php");
    exit();
}

$fecha_inicio = '';
$fecha_fin = '';
$pedidos_totales = [];

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Validar fechas
    if ($fecha_inicio && $fecha_fin && strtotime($fecha_fin) >= strtotime($fecha_inicio)) {
        $stmt = $pdo->prepare("SELECT d.planta, d.turno, d.menu, SUM(d.cantidad) as CantidadTotal 
                               FROM Detalle_Pedidos_Cuyo_Placa d
                               JOIN Pedidos_Cuyo_Placa p ON d.pedido_id = p.id 
                               WHERE p.fecha BETWEEN ? AND ? 
                               GROUP BY d.planta, d.turno, d.menu");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $pedidos_totales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = "Por favor, seleccione un rango de fechas válido.";
    }
}

// Definir las plantas, turnos y menús
$plantas = ['Aglomerado', 'Revestimiento', 'Impregnacion', 'Muebles', 'Transporte (Revestimiento)'];
$turnos_menus = [
    'Mañana' => ['Desayuno día siguiente', 'Almuerzo Caliente', 'Refrigerio sandwich almuerzo'],
    'Tarde' => ['Media tarde', 'Cena caliente', 'Refrigerio sandwich cena'],
    'Noche' => ['Desayuno noche', 'Sandwich noche']
];

// Inicializar array para los totales por planta y menú
$totales_pedidos = [];
$totales_menus = array_fill_keys(array_merge(...array_values($turnos_menus)), 0);

foreach ($plantas as $planta) {
    $totales_pedidos[$planta] = [];
    foreach ($turnos_menus as $turno => $menus) {
        foreach ($menus as $menu) {
            $totales_pedidos[$planta][$menu] = 0;
        }
    }
}

// Rellenar los totales con los resultados obtenidos de la base de datos
foreach ($pedidos_totales as $pedido) {
    $planta = $pedido['planta'];
    $menu = $pedido['menu'];
    $cantidad = $pedido['CantidadTotal'];

    if (isset($totales_pedidos[$planta][$menu])) {
        $totales_pedidos[$planta][$menu] += $cantidad;
        $totales_menus[$menu] += $cantidad;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Cuyo - Cocina</title>
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

        .turno-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-size: 1.2em;
            font-weight: bold;
        }

        .planta-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pedidos de Viandas - Cuyo (Cocina)</h1>

        <?php if (isset($error)) : ?>
            <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post" action="pedidos_cuyo.php">
            <label for="fecha_inicio">Desde:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?php echo htmlspecialchars($fecha_inicio); ?>">

            <label for="fecha_fin">Hasta:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" required value="<?php echo htmlspecialchars($fecha_fin); ?>">

            <button type="submit">Filtrar</button>
        </form>

        <!-- Mostrar KPIs -->
        <div class="kpi-container">
            <?php foreach ($totales_menus as $menu => $total) : ?>
                <div class="kpi">
                    <?php echo htmlspecialchars($menu); ?>
                    <p><?php echo htmlspecialchars($total); ?> viandas</p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Mostrar la tabla combinada -->
        <table>
            <thead>
                <tr>
                    <th rowspan="2" class="planta-header">Planta</th>
                    <th colspan="3" class="turno-header">Mañana</th>
                    <th colspan="3" class="turno-header">Tarde</th>
                    <th colspan="2" class="turno-header">Noche</th>
                </tr>
                <tr>
                    <th>Desayuno día siguiente</th>
                    <th>Almuerzo Caliente</th>
                    <th>Refrigerio sandwich almuerzo</th>
                    <th>Media tarde</th>
                    <th>Cena caliente</th>
                    <th>Refrigerio sandwich cena</th>
                    <th>Desayuno noche</th>
                    <th>Sandwich noche</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plantas as $planta) : ?>
                    <tr>
                        <td class="planta-header"><?php echo htmlspecialchars($planta); ?></td>
                        <!-- Mañana -->
                        <td><?php echo htmlspecialchars($totales_pedidos[$planta]['Desayuno día siguiente']); ?></td>
                        <td><?php echo htmlspecialchars($totales_pedidos[$planta]['Almuerzo Caliente']); ?></td>
                        <td><?php echo htmlspecialchars($totales_pedidos[$planta]['Refrigerio sandwich almuerzo']); ?></td>
                        <!-- Tarde -->
                        <td><?php echo htmlspecialchars($totales_pedidos[$planta]['Media tarde']); ?></td>
                        <td><?php echo htmlspecialchars($totales_pedidos[$planta]['Cena caliente']); ?></td>
                        <td><?php echo htmlspecialchars($totales_pedidos[$planta]['Refrigerio sandwich cena']); ?></td>
                        <!-- Noche -->
                        <td><?php echo htmlspecialchars($totales_pedidos[$planta]['Desayuno noche']); ?></td>
                        <td><?php echo htmlspecialchars($totales_pedidos[$planta]['Sandwich noche']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

