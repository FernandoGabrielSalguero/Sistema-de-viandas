<?php
include '../php/db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Usuario') {
    echo "<script>console.error('Usuario no autorizado o sesión no iniciada');</script>";
    header("Location: login.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$userid = $_SESSION['userid'];

// Obtener los hijos del usuario
$sql = "SELECT * FROM hijos WHERE usuario_id = $userid";
$result = $conn->query($sql);

$hijos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hijos[] = $row;
    }
}

// Obtener el saldo del usuario
$sql = "SELECT saldo FROM usuarios WHERE id = $userid";
$saldo_result = $conn->query($sql);

$saldo = 0;
if ($saldo_result->num_rows > 0) {
    $saldo = $saldo_result->fetch_assoc()['saldo'];
}

// Obtener los menús disponibles
$sql = "SELECT * FROM menus ORDER BY fecha ASC";
$menus_result = $conn->query($sql);

$menus = [];
if ($menus_result->num_rows > 0) {
    while ($row = $menus_result->fetch_assoc()) {
        $menus[$row['fecha']][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>¡Qué gusto verte de nuevo, <?php echo $_SESSION['username']; ?>!</title>

    <style>
        .input-group,
        .menu-day {
            margin-bottom: 20px;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #369456;
            /* Un verde más oscuro para el hover */
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>¡Qué gusto verte de nuevo, <?php echo $_SESSION['username']; ?>!</h1>
        <p>Saldo: $<?php echo number_format($saldo, 2); ?></p>
        <button onclick="location.href='../php/logout.php'">Logout</button>
        <button style="background-color: #25d366;" onclick="window.location.href='https://wa.me/543406173';">Contacto</button>
        <button onclick="location.href='pedidos_realizados.php'">Mis Pedidos</button> <!-- Botón nuevo para Mis Pedidos -->
    </div>

    <?php include 'details_card.php'; ?>

    <div class="container">
        <h3>Seleccionar Viandas</h3>
        <form id="order-form" action="../php/place_order.php" method="POST">
            <div class="input-group">
                <label for="hijo">¿A quién le entregamos el pedido?</label>
                <select id="hijo" name="hijo_id" required>
                    <?php foreach ($hijos as $hijo) : ?>
                        <option value='<?php echo $hijo['id']; ?>' data-curso='<?php echo $hijo['curso_id']; ?>'>
                            <?php echo htmlspecialchars($hijo['nombre']) . " " . htmlspecialchars($hijo['apellido']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label for="menu">Seleccione una vianda por día:</label>
                <div id="menus">
                    <?php foreach ($menus as $fecha => $menu_items) : ?>
                        <div class='menu-day'>
                            <label><?php echo date("d-m-Y", strtotime($fecha)); ?></label>
                            <select name='menu_id[<?php echo $fecha; ?>]' class='menu-select' data-precio-total='0'>
                                <option value='' data-precio='0'>Sin vianda seleccionada</option>
                                <?php foreach ($menu_items as $menu) : ?>
                                    <option value='<?php echo $menu['id']; ?>' data-precio='<?php echo $menu['precio']; ?>'>
                                        <?php echo htmlspecialchars($menu['nombre']) . " (\$" . number_format($menu['precio'], 2) . ")"; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" id="realizarPedido" style="background-color: #4CAF50; color: white; cursor: pointer;">Realizar Pedido</button>

        </form>

        <!-- Pop-up para confirmación de pedido -->
        <div id="orderSummaryPopup" style="display:none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
            <div style="background-color: white; width: 50%; margin: 100px auto; padding: 20px;">
                <h4>Resumen del Pedido</h4>
                <div id="orderDetails"></div>
                <button onclick="submitOrderForm()">Aceptar</button>
                <button onclick="closePopup()">Cerrar</button>
            </div>
        </div>

        <h3>Notas de los Hijos</h3>
        <?php if (!empty($hijos)) : ?>
            <?php foreach ($hijos as $hijo) : ?>
                <p><?php echo htmlspecialchars($hijo['nombre']) . " " . htmlspecialchars($hijo['apellido']); ?> (Curso: <?php echo $hijo['curso_id']; ?>): <?php echo htmlspecialchars($hijo['notas']); ?></p>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No hay notas disponibles</p>
        <?php endif; ?>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('.menu-select');
            selects.forEach(select => {
                select.addEventListener('change', updateTotal);
            });

            function updateTotal() {
                let total = 0;
                selects.forEach(select => {
                    const precio = parseFloat(select.options[select.selectedIndex].getAttribute('data-precio'));
                    if (!isNaN(precio)) {
                        total += precio;
                    }
                });
                const botonPedido = document.getElementById('realizarPedido');
                botonPedido.textContent = `Realizar Pedido - Total: $${total.toFixed(2)}`;
            }
        });


        document.getElementById('order-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting through the browser

            var hijoSelect = document.getElementById('hijo');
            var hijoNombre = hijoSelect.options[hijoSelect.selectedIndex].text;
            var total = 0;
            var detallesPedido = '';

            // Calculate total and collect details
            document.querySelectorAll('.menu-select').forEach(function(select) {
                var precio = parseFloat(select.options[select.selectedIndex].getAttribute('data-precio'));
                if (precio > 0) {
                    total += precio;
                    var fecha = select.parentNode.querySelector('label').textContent;
                    var menu = select.options[select.selectedIndex].text;
                    detallesPedido += `<p>${fecha}: ${menu} - $${precio.toFixed(2)}</p>`;
                }
            });

            // Display details in the popup
            document.getElementById('orderDetails').innerHTML = `
                <p>Alumno: ${hijoNombre}</p>
                ${detallesPedido}
                <p>Total: $${total.toFixed(2)}</p>
                <p>Saldo actual: $${parseFloat(<?php echo json_encode($saldo); ?>).toFixed(2)}</p>
                <p>Total a pagar después del saldo: $${Math.max(0, total - <?php echo json_encode($saldo); ?>).toFixed(2)}</p>
            `;
            document.getElementById('orderSummaryPopup').style.display = 'block';
        });

        function submitOrderForm() {
            document.getElementById('order-form').submit(); // Real form submission
        }

        function closePopup() {
            document.getElementById('orderSummaryPopup').style.display = 'none';
        }
    </script>


</body>

</html>