<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_agencia') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';
include '../includes/header_hyt_agencia.php';

// Obtener los destinos disponibles para el menú desplegable
$stmt_destinos = $pdo->prepare("SELECT id, nombre FROM destinos_hyt");
$stmt_destinos->execute();
$destinos = $stmt_destinos->fetchAll(PDO::FETCH_ASSOC);

// Obtener los productos en venta de la tabla precios_hyt
$stmt_precios = $pdo->prepare("SELECT id, nombre, precio FROM precios_hyt WHERE en_venta = 1");
$stmt_precios->execute();
$productos = $stmt_precios->fetchAll(PDO::FETCH_ASSOC);

// Obtener el hyt_admin asignado a esta agencia
$agencia_id = $_SESSION['usuario_id'];
$stmt_admin = $pdo->prepare("SELECT hyt_admin_id FROM hyt_admin_agencia WHERE hyt_agencia_id = ?");
$stmt_admin->execute([$agencia_id]);
$hyt_admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);
$hyt_admin_id = $hyt_admin['hyt_admin_id'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Realizar Pedido</title>
    <link rel="stylesheet" href="../css/hyt_variables.css">
    <script>
        function mostrarModal() {
            var detallesPedido = '';
            document.querySelectorAll('.producto').forEach(function(producto) {
                var nombre = producto.dataset.nombre;
                var cantidad = producto.value;
                var precio = producto.dataset.precio;
                if (cantidad > 0) {
                    detallesPedido += `<p>${nombre}: ${cantidad} x ${precio} = ${(cantidad * precio).toFixed(2)}</p>`;
                }
            });
            document.getElementById('detallePedido').innerHTML = detallesPedido;
            document.getElementById('modalConfirmacion').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalConfirmacion').style.display = 'none';
        }

        function enviarPedido() {
            document.getElementById('pedidoForm').submit();
        }
    </script>
</head>
<body>

<h1>Realizar un nuevo pedido</h1>

<form id="pedidoForm" method="POST" action="procesar_pedido.php">
    <label for="destino">Seleccionar destino:</label>
    <select id="destino" name="destino" required>
        <?php foreach ($destinos as $destino): ?>
            <option value="<?php echo $destino['id']; ?>"><?php echo $destino['nombre']; ?></option>
        <?php endforeach; ?>
    </select>

    <label for="hora_salida">Hora de salida:</label>
    <input type="time" id="hora_salida" name="hora_salida" required>

    <h2>Detalle del pedido</h2>

    <?php foreach ($productos as $producto): ?>
        <label for="producto_<?php echo $producto['id']; ?>"><?php echo $producto['nombre']; ?> (Precio: <?php echo $producto['precio']; ?>):</label>
        <input type="number" id="producto_<?php echo $producto['id']; ?>" class="producto" name="productos[<?php echo $producto['id']; ?>]" data-nombre="<?php echo $producto['nombre']; ?>" data-precio="<?php echo $producto['precio']; ?>" min="0" value="0">
    <?php endforeach; ?>

    <button type="button" onclick="mostrarModal()">Realizar Pedido</button>
</form>

<!-- Modal de confirmación -->
<div id="modalConfirmacion" style="display:none;">
    <div class="modal-content">
        <h2>Confirmar Pedido</h2>
        <div id="detallePedido"></div>
        <button type="button" onclick="cerrarModal()">Cancelar</button>
        <button type="button" onclick="enviarPedido()">Aceptar</button>
    </div>
</div>

</body>
</html>
