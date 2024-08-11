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
        $stmt = $pdo->prepare("SELECT Planta, Turno, Menu, SUM(Cantidad) as CantidadTotal 
                               FROM Pedidos_Cuyo_Placa 
                               WHERE Fecha BETWEEN ? AND ? 
                               GROUP BY Planta, Turno, Menu");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $pedidos_totales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = "Por favor, seleccione un rango de fechas válido.";
    }
}

// Definir las plantas, turnos y menús
$plantas = ['Aglomerado', 'Revestimiento', 'Impregnacion', 'Muebles', 'Transporte (Revestimiento)'];
$turnos = ['Mañana', 'Tarde', 'Noche'];
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

// Inicializar array para mostrar resultados
$resultados = [];
foreach ($turnos as $turno) {
    $resultados[$turno] = [];
    foreach ($plantas as $planta) {
        $resultados[$turno][$planta] = array_fill_keys($menus, 0);
    }
}

// Rellenar la tabla con los resultados
foreach ($pedidos_totales as $pedido) {
    $turno = $pedido['Turno'];
    $planta = $pedido['Planta'];
    $menu = $pedido['Menu'];
    $cantidad = $pedido['CantidadTotal'];

    if (isset($resultados[$turno][$planta][$menu])) {
        $resultados[$turno][$planta][$menu] += $cantidad;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Cocina</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: white;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .turno-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: left;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 1.2em;
            border-radius: 5px;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard - Cocina</h1>

        <?php if (isset($error)) : ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="post" action="dashboard_cocina.php">
            <label for="fecha_inicio">Desde:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?php echo htmlspecialchars($fecha_inicio); ?>">

            <label for="fecha_fin">Hasta:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" required value="<?php echo htmlspecialchars($fecha_fin); ?>">

            <button type="submit">Filtrar</button>
        </form>

        <?php if (!empty($pedidos_totales)) : ?>
            <?php foreach ($turnos as $turno) : ?>
                <div class="turno-header">
                    Turno: <?php echo htmlspecialchars($turno); ?>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Planta</th>
                            <?php foreach ($menus as $menu) : ?>
                                <th><?php echo htmlspecialchars($menu); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plantas as $planta) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($planta); ?></td>
                                <?php foreach ($menus as $menu) : ?>
                                    <td>
                                        <?php echo htmlspecialchars($resultados[$turno][$planta][$menu]); ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
