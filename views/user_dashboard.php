<?php
include '../php/db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Usuario') {
    echo "<script>console.error('Usuario no autorizado o sesión no iniciada');</script>";
    header("Location: login.php");
    exit();
}

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
</head>
<body>
    <div class="header">
        <h1>¡Qué gusto verte de nuevo, <?php echo $_SESSION['username']; ?>!</h1>
        <p>Saldo: $<?php echo number_format($saldo, 2); ?></p>
        <button onclick="location.href='../php/logout.php'">Logout</button>
        <button style="background-color: #25d366;" onclick="window.location.href='https://wa.me/543406173';">Contacto</button>
    </div>

    <?php include 'details_card.php'; ?>

    <div class="container">
        <h3>Seleccionar Viandas</h3>
        <form id="order-form" action="../php/place_order.php" method="POST">
            <div class="input-group">
                <label for="hijo">¿A quién le entregamos el pedido?</label>
                <select id="hijo" name="hijo_id" required>
                    <?php
                    if (count($hijos) > 0):
                        foreach ($hijos as $hijo):
                            echo "<option value='{$hijo['id']}' data-curso='{$hijo['curso_id']}'>{$hijo['nombre']} {$hijo['apellido']}</option>";
                        endforeach;
                    else:
                        echo "<option value=''>No hay hijos disponibles</option>";
                    endif;
                    ?>
                </select>
            </div>
            <div class="input-group">
                <label for="menu">Seleccione una vianda por día:</label>
                <div id="menus">
                    <?php
                    foreach ($menus as $fecha => $menu_items):
                        $formattedDate = date("d-m-Y", strtotime($fecha));
                        echo "<div class='menu-day'>";
                        echo "<label>{$formattedDate}</label>";
                        echo "<select name='menu_id[{$fecha}]' class='menu-select' data-precio-total='0'>";
                        echo "<option value='' data-precio='0'>Sin vianda seleccionada</option>";
                        foreach ($menu_items as $menu):
                            echo "<option value='{$menu['id']}' data-precio='{$menu['precio']}'>{$menu['nombre']} (\${$menu['precio']})</option>";
                        endforeach;
                        echo "</select>";
                        echo "</div>";
                    endforeach;
                    ?>
                </div>
            </div>
            <button type="submit" style="background-color: #4CAF50; color: white; cursor: pointer;">Realizar Pedido</button>
        </form>

        <h3>Notas de los Hijos</h3>
        <?php
        if (count($hijos) > 0):
            foreach ($hijos as $hijo):
                echo "<p>{$hijo['nombre']} {$hijo['apellido']} (Curso: {$hijo['curso_id']}): {$hijo['notas']}</p>";
            endforeach;
        else:
            echo "<p>No hay notas disponibles</p>";
        endif;
        ?>

        <h3>Pedidos Realizados</h3>
        <table class="material-design-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hijo</th>
                    <th>Menú</th>
                    <th>Fecha de entrega</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>{$row['hijo_nombre']} {$row['hijo_apellido']}</td>";
                        echo "<td>{$row['menu_nombre']}</td>";
                        echo "<td>" . date("d-m-Y", strtotime($row['fecha'])) . "</td>";
                        echo "<td>{$row['estado']}</td>";
                        echo "</tr>";
                    endwhile;
                else:
                    echo "<tr><td colspan='5'>No hay pedidos realizados</td></tr>";
                endif;
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
