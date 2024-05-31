<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Usuario') {
    header('Location: ../index.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/db_connect.php';

// Obtener los hijos del usuario
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $query = "SELECT * FROM hijos WHERE usuario_id = '$usuario_id'";
    $hijos_result = mysqli_query($conn, $query);
} else {
    $mensaje = "Error: No se pudo encontrar el usuario.";
}

// Obtener las fechas únicas de los menús disponibles
$query_fechas = "SELECT DISTINCT fecha FROM menu";
$fechas_result = mysqli_query($conn, $query_fechas);

// Array para almacenar los menús agrupados por fecha
$menus_por_fecha = array();

// Iterar sobre las fechas para obtener los menús correspondientes
while ($fecha_row = mysqli_fetch_assoc($fechas_result)) {
    $fecha = $fecha_row['fecha'];
    $query_menu = "SELECT * FROM menu WHERE fecha = '$fecha'";
    $menu_result = mysqli_query($conn, $query_menu);
    $menus_por_fecha[$fecha] = mysqli_fetch_all($menu_result, MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Vianda</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script>
        function confirmarPedido() {
            if (confirm("¿Estás seguro de querer realizar este pedido?")) {
                document.getElementById("pedido_form").submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Seleccionar Vianda</h1>
        <?php if (isset($mensaje)) : ?>
            <p><?= $mensaje ?></p>
        <?php endif; ?>
        <form id="pedido_form" action="procesar_pedido.php" method="post">
            <div class="form-group">
                <label for="hijo_id">Seleccionar Hijo</label>
                <select id="hijo_id" name="hijo_id" required>
                    <?php if (isset($hijos_result)) : ?>
                        <?php while ($row = mysqli_fetch_assoc($hijos_result)) : ?>
                            <option value="<?= $row['id'] ?>"><?= $row['nombre'] ?> - <?= $row['curso'] ?></option>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <option value="">No se encontraron hijos</option>
                    <?php endif; ?>
                </select>
            </div>
            <?php foreach ($menus_por_fecha as $fecha => $menus) : ?>
                <div class="form-group">
                    <label for="<?= $fecha ?>">Menús del <?= $fecha ?></label>
                    <select id="<?= $fecha ?>" name="vianda_id" required>
                        <option value="">Sin menú elegido</option> <!-- Opción predeterminada -->
                        <?php foreach ($menus as $menu) : ?>
                            <option value="<?= $menu['id'] ?>"><?= $menu['nombre'] ?> - $<?= $menu['precio'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endforeach; ?>
            <?php if (isset($hijos_result)) : ?>
                <button type="button" onclick="confirmarPedido()">Realizar Pedido</button>
            <?php endif; ?>
        </form>
        <a href="dashboard.php">Volver al Dashboard</a>
    </div>
</body>
</html>
