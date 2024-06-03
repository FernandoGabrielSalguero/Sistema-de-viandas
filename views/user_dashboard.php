<?php
include '../php/db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Usuario') {
    echo "<script>console.error('Usuario no autorizado o sesión no iniciada');</script>";
    header("Location: login.php");
    exit();
}

$userid = $_SESSION['userid'];
$userQuery = "SELECT nombre FROM usuarios WHERE id = $userid";
$userInfo = $conn->query($userQuery)->fetch_assoc();

$sql = "SELECT h.*, c.nombre as colegio_nombre, cu.nombre as curso_nombre FROM hijos h
        JOIN colegios c ON h.colegio_id = c.id
        JOIN cursos cu ON h.curso_id = cu.id
        WHERE h.usuario_id = $userid";
$hijos = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

$saldo_result = $conn->query("SELECT saldo FROM usuarios WHERE id = $userid");
$saldo = $saldo_result->num_rows > 0 ? $saldo_result->fetch_assoc()['saldo'] : 0;

$menus_result = $conn->query("SELECT * FROM menus ORDER BY fecha ASC");
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
        <h1>Qué bueno verte de nuevo, <?= $userInfo['nombre']; ?></h1>
        <p>Saldo: $<?= number_format($saldo, 2); ?></p>
        <div style="display: flex; justify-content: space-between; width: 300px;">
            <button onclick="window.location.href='../php/logout.php'">Cerrar sesión</button>
            <button onclick="window.open('https://wa.me/542613406173', '_blank')">Contacto</button>
        </div>
    </div>
    <div class="container">
        <h3>Seleccionar Viandas</h3>
        <form id="order-form" action="../php/place_order.php" method="POST">
            <div class="input-group">
                <label for="hijo">¿A quién le entregamos el pedido?</label>
                <select id="hijo" name="hijo_id" required>
                    <?php foreach ($hijos as $hijo): ?>
                        <option value="<?= $hijo['id']; ?>" data-curso="<?= $hijo['curso_nombre']; ?>"><?= $hijo['nombre'] . ' ' . $hijo['apellido']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label for="menu">Seleccione una vianda por día:</label>
                <div id="menus">
                    <?php foreach ($menus as $fecha => $menu_items): ?>
                        <div class='menu-day'>
                            <label><?= $fecha; ?></label>
                            <select name='menu_id[<?= $fecha; ?>]' class='menu-select' data-precio-total='0' onchange="updateTotal()">
                                <option value='' data-precio='0'>Sin vianda seleccionada</option>
                                <?php foreach ($menu_items as $menu): ?>
                                    <option value='<?= $menu['id']; ?>' data-precio='<?= $menu['precio']; ?>'><?= $menu['nombre']; ?> ($<?= $menu['precio']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit">Realizar Pedido (Total: $<span id="total">0</span>)</button>
        </form>

        <div id="popup" class="popup">
            <div class="popup-content">
                <h4>Resumen del Pedido</h4>
                <p id="resumen-pedido"></p>
                <p>Gracias por confiar en nosotros! Tu pedido se encuentra en estado "En espera de aprobación"...</p>
                <button id="popup-close">Cerrar</button>
                <button onclick="document.getElementById('popup').style.display='none'">Cerrar</button>
            </div>
        </div>

        <h3>Notas de los Hijos</h3>
        <?php foreach ($hijos as $hijo): ?>
            <p><?= $hijo['nombre'] ?> <?= $hijo['apellido'] ?> (<?= $hijo['colegio_nombre'] ?> - <?= $hijo['curso_nombre'] ?>): <?= $hijo['notas'] ?></p>
        <?php endforeach; ?>

        <h3>Pedidos Realizados</h3>
        <table class="material-design-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hijo</th>
                    <th>Menú</th>
                    <th>Fecha de Pedido</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($conn->query("SELECT pedidos.id, hijos.nombre AS hijo_nombre, hijos.apellido AS hijo_apellido, 
                                                 menus.nombre AS menu_nombre, menus.fecha, pedidos.estado
                                          FROM pedidos
                                          JOIN hijos ON pedidos.hijo_id = hijos.id
                                          JOIN menus ON pedidos.menu_id = menus.id
                                          WHERE pedidos.usuario_id = $userid") as $row): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= $row['hijo_nombre'] . ' ' . $row['hijo_apellido']; ?></td>
                        <td><?= $row['menu_nombre']; ?></td>
                        <td><?= $row['fecha']; ?></td>
                        <td><?= $row['estado']; ?></td>
                        <td><button onclick="cancelOrder(<?= $row['id']; ?>)">Cancelar</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        document.querySelectorAll('.menu-select').forEach(select => select.addEventListener('change', updateTotal));

        function updateTotal() {
            let total = Array.from(document.querySelectorAll('.menu-select')).reduce((acc, select) => {
                return acc + parseFloat(select.options[select.selectedIndex].dataset.precio);
            }, 0);
            document.getElementById('total').textContent = total.toFixed(2);
        }

        function cancelOrder(orderId) {
            if (!confirm('¿Está seguro que desea cancelar este pedido?')) return;
            fetch('../php/cancel_order.php', {
                method: 'POST',
                body: JSON.stringify({ order_id: orderId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pedido cancelado exitosamente.');
                    location.reload();
                } else {
                    alert('No se pudo cancelar el pedido.');
                }
            })
            .catch(error => {
                console.error('Error al cancelar pedido:', error);
                alert('Error al tratar de cancelar el pedido.');
            });
        }

        document.getElementById('popup-close').addEventListener('click', () => {
            document.getElementById('popup').style.display = 'none';
        });
    </script>

    <!-- Incluir el resumen del pedido -->
<?php include 'order_summary.php'; ?>
</body>
</html>
