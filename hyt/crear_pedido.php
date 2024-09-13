<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'hyt_agencia') {
    header("Location: ../login.php");
    exit();
}

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Lógica para el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['realizar_pedido'])) {
    $destino_id = $_POST['destino'];
    $hora_salida = $_POST['hora_salida'];
    $interno = $_POST['interno'];
    $observaciones = $_POST['observaciones'];  // Añadir observaciones
    $fecha_pedido = date('Y-m-d');  // Fecha actual
    $estado = 'vigente'; // El pedido comienza como "vigente"

    // Insertar el pedido en la tabla pedidos_hyt
    $stmt_pedido = $pdo->prepare("INSERT INTO pedidos_hyt (nombre_agencia, fecha_pedido, estado, interno, hora_salida, destino_id, hyt_admin_id, observaciones) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    // Ejecución de la consulta y manejo de errores
    if (!$stmt_pedido->execute([$agencia_id, $fecha_pedido, $estado, $interno, $hora_salida, $destino_id, $hyt_admin_id, $observaciones])) {
        // Obtener detalles del error
        $errorInfo = $stmt_pedido->errorInfo();
        echo "Error al realizar el pedido: " . $errorInfo[2];
        exit(); // Detener ejecución si falla la inserción en pedidos_hyt
    } else {
        // Obtener el ID del pedido recién creado
        $pedido_id = $pdo->lastInsertId(); 
        
        // Insertar el detalle del pedido en la tabla detalle_pedidos_hyt
        $stmt_detalle = $pdo->prepare("INSERT INTO detalle_pedidos_hyt (pedido_id, nombre, precio, cantidad) VALUES (?, ?, ?, ?)");
        
        foreach ($_POST['productos'] as $producto_id => $cantidad) {
            if ($cantidad > 0) {
                // Obtener los detalles del producto (nombre y precio)
                $stmt_producto = $pdo->prepare("SELECT nombre, precio FROM precios_hyt WHERE id = ?");
                $stmt_producto->execute([$producto_id]);
                $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);
                
                // Inserción de detalles y manejo de errores
                if (!$stmt_detalle->execute([$pedido_id, $producto['nombre'], $producto['precio'], $cantidad])) {
                    $errorInfo = $stmt_detalle->errorInfo();
                    echo "Error al insertar el detalle del pedido: " . $errorInfo[2];
                    exit(); // Detener ejecución si falla la inserción en detalle_pedidos_hyt
                }
            }
        }

        // Enviar el correo con el detalle del pedido
        $subject = "Detalle del Pedido Realizado";
        $message = "Se ha realizado un pedido con los siguientes detalles:\n\n";
        foreach ($_POST['productos'] as $producto_id => $cantidad) {
            if ($cantidad > 0) {
                $stmt_producto = $pdo->prepare("SELECT nombre FROM precios_hyt WHERE id = ?");
                $stmt_producto->execute([$producto_id]);
                $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);
                $message .= "{$producto['nombre']}: $cantidad\n";
            }
        }
        $to = "correo@cliente.com"; // Reemplazar por el correo del cliente
        if (!mail($to, $subject, $message)) {
            echo "Error al enviar el correo.";
        } else {
            echo "Pedido realizado correctamente y correo enviado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Realizar Pedido</title>
    <link rel="stylesheet" href="../css/hyt_variables.css">
    <style>
        /* Estilos del modal */
        #modalConfirmacion {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        button {
            margin: 10px;
        }
    </style>
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
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>

    <form id="pedidoForm" method="POST" action="">
        <label for="destino">Seleccionar destino:</label>
        <select id="destino" name="destino" required>
            <?php foreach ($destinos as $destino): ?>
                <option value="<?php echo $destino['id']; ?>"><?php echo $destino['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="interno">Interno (Número de interno):</label>
        <input type="number" id="interno" name="interno" required>

        <label for="hora_salida">Hora de salida:</label>
        <input type="time" id="hora_salida" name="hora_salida" required>

        <label for="observaciones">Observaciones:</label>
        <textarea id="observaciones" name="observaciones" rows="4" cols="50" placeholder="Escriba cualquier observación sobre el pedido"></textarea>

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
