<?php
include '../php/db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Usuario') {
    echo "<script>console.error('Usuario no autorizado o sesión no iniciada');</script>";
    header("Location: login.php");
    exit();
}

// Obtener los hijos del usuario
$userid = $_SESSION['userid'];
$sql = "SELECT * FROM hijos WHERE usuario_id = $userid";
$result = $conn->query($sql);

if ($result === FALSE) {
    die("<script>console.error('Error en la consulta de hijos: " . $conn->error . "');</script>");
}

$hijos = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
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
    while($row = $menus_result->fetch_assoc()) {
        $menus[$row['fecha']][] = $row;
    }
}

// Cargar pedidos para posible cancelación
$today = date('Y-m-d');
$currentTime = date('H');
$canCancel = ($currentTime < 9); // Cancelación posible antes de las 9 AM

$sql = "SELECT p.id, m.nombre AS menu_name, p.estado, m.fecha AS menu_date
        FROM pedidos p
        JOIN menus m ON p.menu_id = m.id
        WHERE p.usuario_id = $userid AND p.estado = 'Aprobado' AND m.fecha = '$today'";
$pedidos = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Panel de Usuario - Viandas</title>
</head>
<body>
    <div class="header">
        <h1>Panel de Usuario</h1>
        <p>Saldo: $<?php echo number_format($saldo, 2); ?></p>
        <button onclick="location.href='../php/logout.php'">Logout</button>
        <?php if ($canCancel): ?>
        <button onclick="document.getElementById('cancel-popup').style.display='block'">Cancelar Pedidos</button>
        <?php endif; ?>
    </div>
    <div id="cancel-popup" class="popup" style="display:none;">
        <div class="popup-content">
            <h4>Cancelar Pedidos del Día</h4>
            <?php if ($pedidos->num_rows > 0): ?>
                <form action="../php/cancel_order.php" method="POST">
                    <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                        <p>
                            <?php echo $pedido['menu_name'] . " - " . $pedido['menu_date']; ?>
                            <button type="submit" name="cancel" value="<?php echo $pedido['id']; ?>">Cancelar</button>
                        </p>
                    <?php endwhile; ?>
                </form>
            <?php else: ?>
                <p>No hay pedidos aprobados para cancelar hoy.</p>
            <?php endif; ?>
            <button onclick="document.getElementById('cancel-popup').style.display='none'">Cerrar</button>
        </div>
    </div>
    <div class="container">
        <h2>¡Que gusto verte de nuevo!, <?php echo $_SESSION['username']; ?></h2>
        <h3>Seleccionar Viandas</h3>
        <form id="order-form" action="../php/place_order.php" method="POST">
            <div class="input-group">
                <label for="hijo">¿A quién le entregamos el pedido?</label>
                <select id="hijo" name="hijo_id" required>
                    <?php foreach ($hijos as $hijo): ?>
                        <option value="<?php echo $hijo['id']; ?>" data-curso="<?php echo $hijo['curso_id']; ?>"><?php echo $hijo['nombre'] . ' ' . $hijo['apellido']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label for="menu">Seleccione una vianda por día:</label>
                <div id="menus">
                    <?php foreach ($menus as $fecha => $menu_items): ?>
                        <div class='menu-day'>
                            <label><?php echo $fecha; ?></label>
                            <select name='menu_id[<?php echo $fecha; ?>]' class='menu-select' data-precio-total='0'>
                                <option value='' data-precio='0'>Sin vianda seleccionada</option>
                                <?php foreach ($menu_items as $menu): ?>
                                    <option value='<?php echo $menu['id']; ?>' data-precio='<?php echo $menu['precio']; ?>'><?php echo $menu['nombre'] . ' ($' . $menu['precio'] . ')'; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit">Realizar Pedido</button>
        </form>
    </div>
    <script>
    document.getElementById('order-form').addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Obtener el nombre del hijo seleccionado y su curso
        var hijoSelect = document.getElementById('hijo');
        var hijoNombre = hijoSelect.options[hijoSelect.selectedIndex].text;
        var hijoCurso = hijoSelect.options[hijoSelect.selectedIndex].getAttribute('data-curso');

        // Calcular el precio total y obtener el resumen de viandas
        var total = 0;
        var resumen = '';
        var menus = document.querySelectorAll('.menu-select');
        menus.forEach(function(menu) {
            var selectedOption = menu.options[menu.selectedIndex];
            var precio = parseFloat(selectedOption.getAttribute('data-precio'));
            if (precio > 0) {
                total += precio;
                resumen += '<p>' + selectedOption.text + '</p>';
            }
        });

        // Calcular el monto restante a pagar después de descontar el saldo
        var saldo = <?php echo $saldo; ?>;
        var montoRestante = total - saldo;
        var textoSaldo = '';
        if (montoRestante > 0) {
            textoSaldo = `<p>Saldo utilizado: $${saldo.toFixed(2)}</p><p>Total a transferir: $${montoRestante.toFixed(2)}</p>`;
        } else {
            textoSaldo = `<p>Saldo utilizado: $${total.toFixed(2)}</p><p>No es necesario realizar una transferencia. Su saldo cubre el total del pedido.</p>`;
            montoRestante = 0;
        }

        // Mostrar el resumen en el popup
        document.getElementById('resumen-pedido').innerHTML = `
            <p>Alumno: ${hijoNombre} (Curso: ${hijoCurso})</p>
            ${resumen}
            <p><strong>Total: $${total.toFixed(2)}</strong></p>
            ${textoSaldo}
        `;
        document.getElementById('popup').style.display = 'block';
    });

    document.getElementById('popup-close').addEventListener('click', function() {
        document.getElementById('popup').style.display = 'none';
        document.getElementById('order-form').submit();
    });
    </script>
</body>
</html>
