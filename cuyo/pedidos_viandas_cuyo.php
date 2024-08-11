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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST['fecha'];
    $pedidos = $_POST['pedidos'];

    foreach ($pedidos as $turno => $plantas) {
        foreach ($plantas as $planta => $menus) {
            foreach ($menus as $menu => $cantidad) {
                $stmt = $pdo->prepare("INSERT INTO Pedidos_Cuyo_Placa (Fecha, Planta, Turno, Menu, Cantidad)
                                       VALUES (?, ?, ?, ?, ?)
                                       ON DUPLICATE KEY UPDATE Cantidad = VALUES(Cantidad)");
                $stmt->execute([$fecha, $planta, $turno, $menu, $cantidad]);
            }
        }
    }
    $success = "Pedidos guardados con éxito.";
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

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Viandas Cuyo Placa</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
        }

        form {
            max-width: 1900px;
            width: 100%;
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
        }

        .turno-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: left;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 1.2em;
        }

        .success {
            color: green;
            text-align: center;
            margin-bottom: 20px;
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

        input[type="number"] {
            width: 60px;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Pedidos de Viandas - Cuyo Placa</h1>

        <?php if (isset($success)) : ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <form method="post" action="pedidos_viandas_cuyo.php">
            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" required>

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
                                        <input type="number" name="pedidos[<?php echo $turno; ?>][<?php echo $planta; ?>][<?php echo $menu; ?>]" min="0" value="0">
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>

            <button type="submit">Guardar Pedidos</button>
        </form>
    </div>
</body>
</html>
