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
$usuario_id = $_SESSION['usuario_id'];
$query = "SELECT * FROM hijos WHERE usuario_id = '$usuario_id'";
$hijos_result = mysqli_query($conn, $query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Procesar la compra de viandas
    $hijo_id = $_POST['hijo_id'];
    $fecha = $_POST['fecha'];

    // Obtener viandas disponibles para la fecha seleccionada
    $query = "SELECT * FROM viandas WHERE fecha = '$fecha'";
    $viandas_result = mysqli_query($conn, $query);

    if (mysqli_num_rows($viandas_result) > 0) {
        // Se encontraron viandas para la fecha seleccionada
        while ($row = mysqli_fetch_assoc($viandas_result)) {
            $viandas_disponibles[] = $row;
        }
    } else {
        // No se encontraron viandas para la fecha seleccionada
        $mensaje = "No hay viandas disponibles para la fecha seleccionada.";
    }

    // Verificar si el usuario tiene saldo suficiente
    $query = "SELECT saldo FROM usuarios WHERE id = '$usuario_id'";
    $saldo_result = mysqli_query($conn, $query);
    $saldo = mysqli_fetch_assoc($saldo_result)['saldo'];

    // Comprobar si se seleccionó una vianda
    if (isset($_POST['vianda_id'])) {
        $vianda_id = $_POST['vianda_id'];
        $query = "SELECT precio FROM viandas WHERE id = '$vianda_id'";
        $precio_result = mysqli_query($conn, $query);
        $precio = mysqli_fetch_assoc($precio_result)['precio'];

        if ($saldo >= $precio) {
            // Deduct saldo
            $nuevo_saldo = $saldo - $precio;
            $query = "UPDATE usuarios SET saldo = '$nuevo_saldo' WHERE id = '$usuario_id'";
            mysqli_query($conn, $query);

            // Insertar en la tabla de pedidos
            $query = "INSERT INTO pedidos (usuario_id, hijo_id, vianda_id, fecha) VALUES ('$usuario_id', '$hijo_id', '$vianda_id', '$fecha')";
            if (mysqli_query($conn, $query)) {
                $mensaje = "Vianda seleccionada con éxito.";
            } else {
                $mensaje = "Error: " . mysqli_error($conn);
            }
        } else {
            $mensaje = "Saldo insuficiente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Vianda</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Seleccionar Vianda</h1>
        <?php if (isset($mensaje)) : ?>
            <p><?= $mensaje ?></p>
        <?php endif; ?>
        <form action="seleccionar_vianda.php" method="post">
            <div class="form-group">
                <label for="hijo_id">Seleccionar Hijo</label>
                <select id="hijo_id" name="hijo_id" required>
                    <?php while ($row = mysqli_fetch_assoc($hijos_result)) : ?>
                        <option value="<?= $row['id'] ?>"><?= $row['nombre'] ?> - <?= $row['curso'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php if (isset($viandas_disponibles)) : ?>
                <div class="form-group">
                    <label for="vianda_id">Seleccionar Vianda</label>
                    <select id="vianda_id" name="vianda_id" required>
                        <?php foreach ($viandas_disponibles as $vianda) : ?>
                            <option value="<?= $vianda['id'] ?>"><?= $vianda['nombre'] ?> - $<?= $vianda['precio'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="fecha">Fecha</label>
                <input type="date" id="fecha" name="fecha" required>
            </div>
            <button type="submit">Seleccionar Vianda</button>
        </form>
        <a href="dashboard.php">Volver al Dashboard</a>
    </div>
</body>
</html>
