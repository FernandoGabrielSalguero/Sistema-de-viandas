<?php
include '../php/db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Usuario') {
    header("Location: login.php");
    exit();
}

// Obtener los hijos del usuario
$userid = $_SESSION['userid'];
$sql = "SELECT * FROM hijos WHERE usuario_id = $userid";
$result = $conn->query($sql);
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
    </div>
    <div class="container">
        <h2>Bienvenido, <?php echo $_SESSION['username']; ?></h2>

        <h3>Seleccionar Viandas</h3>
        <form id="order-form" action="../php/place_order.php" method="POST">
            <div class="input-group">
                <label for="hijo">¿A quién le entregamos el pedido?</label>
                <select id="hijo" name="hijo_id" required>
                    <?php foreach ($hijos as $hijo): ?>
                        <option value="<?php echo $hijo['id']; ?>" data-curso="<?php echo $hijo['curso']; ?>"><?php echo $hijo['nombre'] . ' ' . $hijo['apellido']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label for="menu">Seleccione una vianda por día:</label>
                <div id="menus">
                    <?php foreach ($menus as $fecha => $menu_items): ?>
                        <div class="menu-day">
                            <label><?php echo $fecha; ?></label>
                            <select name="menu_id[<?php echo $fecha; ?>]" class="menu-select" data-precio-total="0">
                                <option value="" data-precio="0">Sin vianda seleccionada</option>
                                <?php foreach ($menu_items as $menu): ?>
                                    <option value="<?php echo $menu['id']; ?>" data-precio="<?php echo $menu['precio']; ?>"><?php echo $menu['nombre'] . ' ($' . $menu['precio'] . ')'; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit">Realizar Pedido</button>
        </form>

        <div id="popup" class="popup">
            <div class="popup-content">
                <h4>Resumen del Pedido</h4>
                <p id="resumen-pedido"></p>
                <p>Gracias por confiar en nosotros! Tu pedido se encuentra en estado "En espera de aprobación" eso significa que estamos esperando la transferencia del saldo para poder aprobar el encargo. Recordá que podes hacerlo al siguiente CBU: 0340300408300313721004 a nombre de: Federico Figueroa en el banco: BANCO PATAGONIA, CUIT: 20273627651 Alias: ROJO.GENIO.CASINO. La aprobación puede demorar hasta 48 hs en efectuarse. Cuando esté aprobada, el estado de tu pedido será: APROBADO</p>
                <button id="popup-close">Aceptar</button>
            </div>
        </div>

        <h3>Pedidos Realizados</h3>
        <table class="material-design-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hijo</th>
                    <th>Menú</th>
                    <th>Fecha de Pedido</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT pedidos.id, hijos.nombre AS hijo_nombre, hijos.apellido AS hijo_apellido, 
                               menus.nombre AS menu_nombre, menus.fecha, pedidos.estado
                        FROM pedidos
                        JOIN hijos ON pedidos.hijo_id = hijos.id
                        JOIN menus ON pedidos.menu_id = menus.id
                        WHERE pedidos.usuario_id = $userid";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['hijo_nombre'] . " " . $row['hijo_apellido'] . "</td>";
                        echo "<td>" . $row['menu_nombre'] . "</td>";
                        echo "<td>" . $row['fecha'] . "</td>";
                        echo "<td>" . $row['estado'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay pedidos realizados</td></tr>";
                }
                ?>
            </tbody>
        </table>
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

        // Mostrar el resumen en el popup
        document.getElementById('resumen-pedido').innerHTML = `
            <p>Alumno: ${hijoNombre} (Curso: ${hijoCurso})</p>
            ${resumen}
            <p><strong>Total: $${total.toFixed(2)}</strong></p>
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
