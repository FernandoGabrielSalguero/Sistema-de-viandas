<?php
session_start();
include '../includes/header_cuyo_placa.php';
include '../includes/db.php';
include '../includes/load_env.php';

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar variables del archivo .env
loadEnv(__DIR__ . '/../.env');

// Función para enviar correo electrónico usando SMTP y HTML
function enviarCorreo($to, $subject, $message) {
    $headers = "From: " . getenv('SMTP_USERNAME') . "\r\n" .
               "Reply-To: " . getenv('SMTP_USERNAME') . "\r\n" .
               "Content-Type: text/html; charset=UTF-8\r\n" .
               "X-Mailer: PHP/" . phpversion();

    ini_set('SMTP', getenv('SMTP_HOST'));
    ini_set('smtp_port', getenv('SMTP_PORT'));
    ini_set('sendmail_from', getenv('SMTP_USERNAME'));

    return mail($to, $subject, $message, $headers);
}

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cuyo_placa') {
    header("Location: ../index.php");
    exit();
}

$resumen_pedido = [];
$fecha_pedido = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST['fecha'];
    $fecha_pedido = $fecha;
    $pedidos = $_POST['pedidos'];

    // Iniciar transacción
    $pdo->beginTransaction();

    try {
        // Insertar el nuevo pedido en la tabla Pedidos_Cuyo_Placa
        $stmt = $pdo->prepare("INSERT INTO Pedidos_Cuyo_Placa (usuario_id, fecha, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$_SESSION['usuario_id'], $fecha]);

        // Obtener el ID del pedido recién insertado
        $pedido_id = $pdo->lastInsertId();

        foreach ($pedidos as $turno => $plantas) {
            foreach ($plantas as $planta => $menus) {
                foreach ($menus as $menu => $cantidad) {
                    if ($cantidad > 0) {  // Solo guardar cantidades mayores a 0
                        // Insertar cada detalle del pedido en la tabla Detalle_Pedidos_Cuyo_Placa
                        $stmt = $pdo->prepare("INSERT INTO Detalle_Pedidos_Cuyo_Placa (pedido_id, planta, turno, menu, cantidad)
                                               VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$pedido_id, $planta, $turno, $menu, $cantidad]);

                        // Agregar detalle al resumen
                        $resumen_pedido[] = [
                            'planta' => $planta,
                            'turno' => $turno,
                            'menu' => $menu,
                            'cantidad' => $cantidad
                        ];
                    }
                }
            }
        }

        // Confirmar la transacción
        $pdo->commit();
        $success = true; // Indicar que el pedido se guardó con éxito

        // Construir el mensaje del correo en HTML
        $subject = "Resumen de Pedido de Viandas - ID Pedido: " . $pedido_id;
        $message = "<html><body>";
        $message .= "<h1>Resumen de Pedido de Viandas - ID Pedido: " . $pedido_id . "</h1>";
        $message .= "<p><strong>Fecha del Pedido:</strong> $fecha_pedido</p>";
        $message .= "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        $message .= "<thead><tr>";
        $message .= "<th>Planta</th><th>Turno</th><th>Menú</th><th>Cantidad</th>";
        $message .= "</tr></thead><tbody>";

        foreach ($resumen_pedido as $detalle) {
            $message .= "<tr>";
            $message .= "<td>{$detalle['planta']}</td>";
            $message .= "<td>{$detalle['turno']}</td>";
            $message .= "<td>{$detalle['menu']}</td>";
            $message .= "<td>{$detalle['cantidad']}</td>";
            $message .= "</tr>";
        }

        $message .= "</tbody></table>";
        $message .= "</body></html>";

        // Enviar correo a los destinatarios
        $to = "fernandosalguero685@gmail.com, florenciaivonnediaz@gmail.com, asd@gmail.com, federicofigeroa400@gmail.com";
        if (!enviarCorreo($to, $subject, $message)) {
            echo "Error al enviar el correo.";
        } else {
            echo "Pedido realizado correctamente y correo enviado.";
        }

    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        $error = "Hubo un problema al guardar el pedido: " . $e->getMessage();
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
    <title>Historial de pedidos de viandas</title>
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
    <h1>Historial de pedidos</h1>

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
                                        <?php 
                                        $total = 0;
                                        foreach ($turnos[$turno] as $pedido) : 
                                            $total += $pedido['cantidad'];
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($pedido['menu']); ?></td>
                                                <td><?php echo htmlspecialchars($pedido['cantidad']); ?></td>
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
