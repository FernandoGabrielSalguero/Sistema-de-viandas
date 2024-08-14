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

// Procesar el formulario de filtrado
$fecha_inicio = '';
$fecha_fin = '';
$pedidos = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Validar fechas
    if ($fecha_inicio && $fecha_fin && strtotime($fecha_fin) >= strtotime($fecha_inicio)) {
        // Obtener los pedidos y detalles de la base de datos
        $stmt = $pdo->prepare("SELECT p.id, p.fecha, d.planta, d.turno, d.menu, d.cantidad 
                               FROM Pedidos_Cuyo_Placa p 
                               JOIN Detalle_Pedidos_Cuyo_Placa d ON p.id = d.pedido_id 
                               WHERE p.fecha BETWEEN ? AND ?");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = "Por favor, seleccione un rango de fechas válido.";
    }
}

// Agrupar los pedidos por fecha
$pedidos_agrupados = [];
foreach ($pedidos as $pedido) {
    $fecha = $pedido['fecha'];
    if (!isset($pedidos_agrupados[$fecha])) {
        $pedidos_agrupados[$fecha] = [];
    }
    $pedidos_agrupados[$fecha][] = $pedido;
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
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.2em;
            color: #007bff;
        }

        .card p {
            margin: 0;
            font-size: 0.9em;
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

    <div class="card-container">
        <?php if (!empty($pedidos_agrupados)) : ?>
            <?php foreach ($pedidos_agrupados as $fecha => $pedidos) : ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($fecha); ?></h3>
                    <?php foreach ($pedidos as $pedido) : ?>
                        <p><strong>Planta:</strong> <?php echo htmlspecialchars($pedido['planta']); ?></p>
                        <p><strong>Turno:</strong> <?php echo htmlspecialchars($pedido['turno']); ?></p>
                        <p><strong>Menú:</strong> <?php echo htmlspecialchars($pedido['menu']); ?></p>
                        <p><strong>Cantidad:</strong> <?php echo htmlspecialchars($pedido['cantidad']); ?></p>
                        <hr>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p style="text-align: center;">No hay pedidos para mostrar en el rango de fechas seleccionado.</p>
        <?php endif; ?>
    </div>
</body>
</html>
