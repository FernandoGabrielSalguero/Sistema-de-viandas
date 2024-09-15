<?php
// Iniciar el buffer de salida para evitar el error de headers
ob_start();

// Conexión a la base de datos
include '../includes/header_admin.php';
include '../includes/db.php';

// Modificar estado_saldo (Pagado/Adeudado)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_estado_saldo'])) {
    $pedido_id = $_POST['pedido_id'];
    $nuevo_estado_saldo = $_POST['estado_saldo'] == 'Pagado' ? 'Adeudado' : 'Pagado';

    $stmt = $pdo->prepare("UPDATE pedidos_hyt SET estado_saldo = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado_saldo, $pedido_id]);
    
    // Redirigir a la misma página para evitar reenvío del formulario
    header("Location: estado_saldo_hyt.php");
    exit();  // Detener ejecución para evitar más salidas
}

// Obtener datos de las tablas pedidos_hyt y detalle_pedidos_hyt, agrupados por ID de pedido
$query = "SELECT p.id, p.nombre_agencia, p.fecha_pedido, p.fecha_modificacion, p.estado_saldo, 
                 GROUP_CONCAT(d.nombre SEPARATOR ', ') AS productos, 
                 GROUP_CONCAT(d.cantidad SEPARATOR ', ') AS cantidades, 
                 SUM(d.cantidad * d.precio) AS total
          FROM pedidos_hyt p
          LEFT JOIN detalle_pedidos_hyt d ON p.id = d.pedido_id
          GROUP BY p.id";
$stmt = $pdo->prepare($query);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular los totales "Adeudado" y "Pagado"
$query_totales = "SELECT estado_saldo, SUM(d.cantidad * d.precio) AS total
                  FROM pedidos_hyt p
                  LEFT JOIN detalle_pedidos_hyt d ON p.id = d.pedido_id
                  GROUP BY estado_saldo";
$stmt_totales = $pdo->prepare($query_totales);
$stmt_totales->execute();
$totales = $stmt_totales->fetchAll(PDO::FETCH_ASSOC);

// Inicializar variables para totales
$total_adeudado = 0;
$total_pagado = 0;

foreach ($totales as $total) {
    if ($total['estado_saldo'] == 'Adeudado') {
        $total_adeudado = $total['total'];
    } elseif ($total['estado_saldo'] == 'Pagado') {
        $total_pagado = $total['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Saldo HYT</title>
    <link rel="stylesheet" href="../css/style_estado_saldo_hyt.css">
</head>
<body>

<h1>Gestión de Estado de Saldo</h1>

<!-- KPI: Totales de Adeudado y Pagado -->
<div class="kpi-container">
    <div class="kpi">
        <h3>Total Adeudado</h3>
        <p><?php echo number_format($total_adeudado, 2); ?> ARS</p>
    </div>
    <div class="kpi">
        <h3>Total Pagado</h3>
        <p><?php echo number_format($total_pagado, 2); ?> ARS</p>
    </div>
</div>

<!-- Tabla de pedidos -->
<table class="estado-saldo-table">
    <thead>
        <tr>
            <th>ID Pedido</th>
            <th>Agencia</th>
            <th>Fecha Pedido</th>
            <th>Fecha Modificación</th>
            <th>Productos (Cantidades)</th>
            <th>Total</th>
            <th>Estado de Saldo</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pedidos as $pedido): ?>
            <tr>
                <td><?php echo htmlspecialchars($pedido['id']); ?></td>
                <td><?php echo htmlspecialchars($pedido['nombre_agencia']); ?></td>
                <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                <td><?php echo htmlspecialchars($pedido['fecha_modificacion']); ?></td>
                <td>
                    <?php 
                    $productos = explode(", ", $pedido['productos']);
                    $cantidades = explode(", ", $pedido['cantidades']);
                    foreach ($productos as $index => $producto) {
                        echo htmlspecialchars($producto) . " (" . htmlspecialchars($cantidades[$index]) . ")<br>";
                    }
                    ?>
                </td>
                <td><?php echo number_format($pedido['total'], 2); ?> ARS</td>
                <td>
                    <form method="POST" action="estado_saldo_hyt.php">
                        <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                        <input type="hidden" name="estado_saldo" value="<?php echo $pedido['estado_saldo']; ?>">
                        <label class="switch">
                            <input type="checkbox" <?php echo $pedido['estado_saldo'] == 'Pagado' ? 'checked' : ''; ?>
                                   onchange="this.form.submit()">
                            <span class="slider round"></span>
                        </label>
                        <input type="hidden" name="cambiar_estado_saldo" value="1">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>

<?php
// Finalizar el buffer de salida
ob_end_flush();
