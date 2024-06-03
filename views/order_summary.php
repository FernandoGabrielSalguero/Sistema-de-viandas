<?php
session_start();
// Asegúrate de que el usuario está autenticado y de que los datos necesarios están presentes
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

// Recibir datos enviados desde el formulario
$hijoNombre = $_GET['hijoNombre'] ?? 'No especificado';
$hijoCurso = $_GET['hijoCurso'] ?? 'No especificado';
$menus = $_GET['menus'] ?? [];
$total = $_GET['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Resumen del Pedido</title>
</head>
<body>
    <div class="container">
        <div class="popup">
            <div class="popup-content">
                <h4>Resumen del Pedido</h4>
                <p>Alumno: <?php echo htmlspecialchars($hijoNombre); ?> (Curso: <?php echo htmlspecialchars($hijoCurso); ?>)</p>
                <p>Menús seleccionados:</p>
                <?php
                if (!empty($menus)) {
                    echo '<ul>';
                    foreach ($menus as $fecha => $menu) {
                        echo "<li>{$fecha}: {$menu}</li>";
                    }
                    echo '</ul>';
                } else {
                    echo "<p>No se han seleccionado menús.</p>";
                }
                ?>
                <p><strong>Total: $<?php echo number_format($total, 2); ?></strong></p>
                <p>Gracias por confiar en nosotros! Tu pedido se encuentra en estado "En espera de aprobación" eso significa que estamos esperando la transferencia del saldo para poder aprobar el encargo. Recordá que podes hacerlo al siguiente CBU: 0340300408300313721004 a nombre de: Federico Figueroa en el banco: BANCO PATAGONIA, CUIT: 20273627651 Alias: ROJO.GENIO.CASINO. La aprobación puede demorar hasta 48 hs en efectuarse. Cuando esté aprobada, el estado de tu pedido será: APROBADO</p>
                <button onclick="window.close();">Cerrar</button>
            </div>
        </div>
    </div>
</body>
</html>
