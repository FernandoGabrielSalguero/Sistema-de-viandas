<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/header_cuyo_placa.php';
include '../includes/db.php';

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cuyo_placa') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST['fecha'];
    $pedidos = $_POST['pedidos'];

    foreach ($pedidos as $planta => $turnos) {
        foreach ($turnos as $turno => $menus) {
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

// Definir las plantas y los menús
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
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Pedidos de Viandas - Cuyo Placa</h1>

    <?php if (isset($success)) : ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="post" action="pedidos_viandas_cuyo.php">
        <label for="fecha">Fecha:</label>
        <input type="date" id="fecha" name="fecha" required>
        
        <table border="1">
            <thead>
                <tr>
                    <th>Planta</th>
                    <th>Turno</th>
                    <?php foreach ($menus as $menu) : ?>
                        <th><?php echo htmlspecialchars($menu); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plantas as $planta) : ?>
                    <?php foreach ($turnos as $turno) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($planta); ?></td>
                            <td><?php echo htmlspecialchars($turno); ?></td>
                            <?php foreach ($menus as $menu) : ?>
                                <td>
                                    <input type="number" name="pedidos[<?php echo $planta; ?>][<?php echo $turno; ?>][<?php echo $menu; ?>]" min="0" value="0">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit">Guardar Pedidos</button>
    </form>
</body>
</html>
