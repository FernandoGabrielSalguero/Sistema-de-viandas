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
$turnos_menus = [
    'Mañana' => ['Desayuno día siguiente', 'Almuerzo Caliente'],
    'Tarde' => ['Media tarde', 'Refrigerio sandwich almuerzo'],
    'Noche' => ['Cena caliente', 'Refrigerio sandwich cena', 'Desayuno noche', 'Sandwich noche']
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Viandas Cuyo Placa</title>
    <link rel="stylesheet" href="../css/cuyo_placas.css">
    <style>
        /* Estilos adicionales si es necesario */
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

            <table>
                <thead>
                    <tr>
                        <th>Turno</th>
                        <th>Planta</th>
                        <?php
                        $all_menus = [];
                        foreach ($turnos_menus as $turno => $menus) {
                            foreach ($menus as $menu) {
                                if (!in_array($menu, $all_menus)) {
                                    $all_menus[] = $menu;
                                }
                            }
                        }
                        foreach ($all_menus as $menu) {
                            echo "<th>$menu</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plantas as $planta) : ?>
                        <?php foreach ($turnos_menus as $turno => $menus) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($turno); ?></td>
                                <td><?php echo htmlspecialchars($planta); ?></td>
                                <?php foreach ($all_menus as $menu) : ?>
                                    <td>
                                        <?php if (in_array($menu, $menus)) : ?>
                                            <input type="number" name="pedidos[<?php echo $turno; ?>][<?php echo $planta; ?>][<?php echo $menu; ?>]" min="0" value="0">
                                        <?php else : ?>
                                            <!-- Si el menú no corresponde al turno, deja la celda vacía -->
                                            -
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit">Guardar Pedidos</button>
        </form>
    </div>
</body>
</html>
