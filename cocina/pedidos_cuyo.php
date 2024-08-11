<?php
session_start();
include '../includes/header_cocina.php'; // Asegúrate de que este archivo header sea específico para el rol de cocina
include '../includes/db.php';

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
        $stmt = $pdo->prepare("SELECT Planta, Menu, SUM(Cantidad) as CantidadTotal 
                               FROM Pedidos_Cuyo_Placa 
                               WHERE Fecha BETWEEN ? AND ? 
                               GROUP BY Planta, Menu");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $pedidos_totales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = "Por favor, seleccione un rango de fechas válido.";
    }
}

// Definir las plantas y menús
$plantas = ['Aglomerado', 'Revestimiento', 'Impregnacion', 'Muebles', 'Transporte (Revestimiento)'];
$menus = [
    'Desayuno día siguiente',
    'Almuerzo Caliente',
    'Media tarde',
    'Refrigerio sandwich almuerzo',
    'Cena caliente',
    'Refrigerio sandwich cena',
    'Desayuno noche',
    'Sandwich noche'
];

// Inicializar array para los totales por planta y menú
$totales_pedidos = [];
foreach ($plantas as $planta) {
    $totales_pedidos[$planta] = array_fill_keys($menus, 0);
}

// Rellenar los totales con los resultados obtenidos de la base de datos
foreach ($pedidos_totales as $pedido) {
    $planta = $pedido['Planta'];
    $menu = $pedido['Menu'];
    $cantidad = $pedido['CantidadTotal'];

    if (isset($totales_pedidos[$planta][$menu])) {
        $totales_pedidos[$planta][$menu] += $cantidad;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Cuyo - Cocina</title>
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
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 20px;
        }

        .kpi-group {
            margin-bottom: 30px;
            width: 100%;
        }

        .kpi-group h2 {
            background-color: #007bff;
            color: white;
            padding: 10px;
            margin: 0;
            text-align: center;
            border-radius: 5px;
        }

        .kpi {
            background-color: #007bff;
            color: white;
            padding: 20px;
            margin: 10px;
            border-radius: 5px;
            text-align: center;
            flex: 1;
            min-width: 200px;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Pedidos de Viandas - Cuyo (Cocina)</h1>

        <?php if (isset($error)) : ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="post" action="pedidos_cuyo.php">
            <label for="fecha_inicio">Desde:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?php echo htmlspecialchars($fecha_inicio); ?>">

            <label for="fecha_fin">Hasta:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" required value="<?php echo htmlspecialchars($fecha_fin); ?>">

            <button type="submit">Filtrar</button>
        </form>

        <?php if (!empty($pedidos_totales)) : ?>
            <?php foreach ($plantas as $planta) : ?>
                <div class="kpi-group">
                    <h2><?php echo htmlspecialchars($planta); ?></h2>
                    <div class="kpi-container">
                        <?php foreach ($menus as $menu) : ?>
                            <div class="kpi">
                                <h3><?php echo htmlspecialchars($menu); ?></h3>
                                <p><?php echo htmlspecialchars($totales_pedidos[$planta][$menu]); ?> viandas</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
