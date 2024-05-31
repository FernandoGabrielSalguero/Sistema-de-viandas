<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Usuario') {
    header('Location: ../index.php');
    exit;
}

include '../includes/db_connect.php';

// Obtener los hijos del usuario
$usuario_id = $_SESSION['usuario_id'];
$query = "SELECT * FROM hijos WHERE usuario_id='$usuario_id'";
$result_hijos = mysqli_query($conn, $query);

// Obtener el menú semanal
$query_menu = "SELECT * FROM menu";
$result_menu = mysqli_query($conn, $query_menu);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Viandas - Usuario</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Seleccionar Viandas</h1>
        <form action="comprar_vianda.php" method="post">
            <div class="form-group">
                <label for="hijo">Seleccionar Hijo</label>
                <select id="hijo" name="hijo_id" required>
                    <?php while ($row = mysqli_fetch_assoc($result_hijos)) : ?>
                        <option value="<?= $row['id'] ?>"><?= $row['nombre'] ?> (Curso: <?= $row['curso'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="fecha">Fecha</label>
                <select id="fecha" name="fecha" required>
                    <?php while ($row = mysqli_fetch_assoc($result_menu)) : ?>
                        <option value="<?= $row['fecha'] ?>"><?= $row['fecha'] ?> - <?= $row['nombre'] ?> ($<?= $row['precio'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit">Comprar Vianda</button>
        </form>
        <a href="dashboard.php">Volver al Dashboard</a>
    </div>
</body>
</html>
