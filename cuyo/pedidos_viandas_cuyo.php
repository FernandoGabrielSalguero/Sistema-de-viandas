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

// Verifica si el correo electrónico del usuario está disponible en la sesión
$usuario_email = isset($_SESSION['usuario_email']) ? $_SESSION['usuario_email'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST['fecha'];
    $pedidos = $_POST['pedidos'];
    $detalle_pedidos = '';

    foreach ($pedidos as $turno => $plantas) {
        foreach ($plantas as $planta => $menus) {
            foreach ($menus as $menu => $cantidad) {
                $stmt = $pdo->prepare("INSERT INTO Pedidos_Cuyo_Placa (Fecha, Planta, Turno, Menu, Cantidad)
                                       VALUES (?, ?, ?, ?, ?)
                                       ON DUPLICATE KEY UPDATE Cantidad = VALUES(Cantidad)");
                $stmt->execute([$fecha, $planta, $turno, $menu, $cantidad]);

                // Construir el detalle del pedido para el correo electrónico
                $detalle_pedidos .= "Planta: $planta, Turno: $turno, Menu: $menu, Cantidad: $cantidad\n";
            }
        }
    }
    $success = true; // Indicar que el pedido se guardó con éxito

    // Enviar correo con el detalle del pedido si el correo del usuario está disponible
    if ($usuario_email) {
        $asunto = "Detalle de Pedido de Viandas - Cuyo Placa";
        $mensaje = "Estimado usuario,\n\nSe ha registrado el siguiente pedido de viandas para la fecha $fecha:\n\n$detalle_pedidos\n\nSaludos cordiales.";
        $headers = "From: no-reply@cuyoplaca.com";

        if (!mail($usuario_email, $asunto, $mensaje, $headers)) {
            echo "Error al enviar el correo.";
        } else {
            echo "Correo enviado exitosamente.";
        }
    } else {
        error_log("El correo electrónico del usuario no está disponible. No se pudo enviar el detalle del pedido.");
        echo "El correo electrónico del usuario no está disponible. No se pudo enviar el detalle del pedido.";
    }
}

// Definir las plantas, turnos y menús
$plantas = ['Aglomerado', 'Revestimiento', 'Impregnacion', 'Muebles', 'Transporte (Revestimiento)'];
$turnos_menus = [
    'Mañana' => ['Desayuno día siguiente', 'Almuerzo Caliente', 'Refrigerio sandwich almuerzo'],
    'Tarde' => ['Media tarde', 'Cena caliente', 'Refrigerio sandwich cena'],
    'Noche' => ['Desayuno noche', 'Sandwich noche']
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Viandas Cuyo Placa</title>
    <link rel="stylesheet" href="../css/cuyo_placas.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        th[colspan] {
            background-color: #d9e5f3;
        }
        #fecha {
            font-size: 1.5em; /* Aumentar el tamaño de la fecha */
            padding: 10px; /* Añadir relleno para hacerlo más visible */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pedidos de Viandas - Cuyo Placa</h1>

        <?php if (isset($success) && $success) : ?>
            <p>Pedidos guardados con éxito.</p>
        <?php endif; ?>

        <form method="post" action="pedidos_viandas_cuyo.php">
            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" required>

            <table>
                <thead>
                    <tr>
                        <th rowspan="2">Planta</th>
                        <th colspan="3">Mañana</th>
                        <th colspan="3">Tarde</th>
                        <th colspan="2">Noche</th>
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
                            <td><?php echo htmlspecialchars($planta); ?></td>
                            <!-- Mañana -->
                            <td><input type="number" name="pedidos[Mañana][<?php echo $planta; ?>][Desayuno día siguiente]" min="0" value="0"></td>
                            <td><input type="number" name="pedidos[Mañana][<?php echo $planta; ?>][Almuerzo Caliente]" min="0" value="0"></td>
                            <td><input type="number" name="pedidos[Mañana][<?php echo $planta; ?>][Refrigerio sandwich almuerzo]" min="0" value="0"></td>
                            <!-- Tarde -->
                            <td><input type="number" name="pedidos[Tarde][<?php echo $planta; ?>][Media tarde]" min="0" value="0"></td>
                            <td><input type="number" name="pedidos[Tarde][<?php echo $planta; ?>][Cena caliente]" min="0" value="0"></td>
                            <td><input type="number" name="pedidos[Tarde][<?php echo $planta; ?>][Refrigerio sandwich cena]" min="0" value="0"></td>
                            <!-- Noche -->
                            <td><input type="number" name="pedidos[Noche][<?php echo $planta; ?>][Desayuno noche]" min="0" value="0"></td>
                            <td><input type="number" name="pedidos[Noche][<?php echo $planta; ?>][Sandwich noche]" min="0" value="0"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit">Guardar Pedidos</button>
        </form>
    </div>
</body>
</html>
