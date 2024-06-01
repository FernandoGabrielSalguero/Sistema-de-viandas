<?php
include 'db.php';

if (!isset($_GET['id'])) {
    die("ID del menú no especificado.");
}

$id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $fecha = $_POST['fecha'];

    // Actualizar menú
    $sql = "UPDATE menus SET nombre='$nombre', precio='$precio', fecha='$fecha' WHERE id=$id";
    $conn->query($sql);

    header("Location: ../views/manage_menus.php");
    exit();
} else {
    // Obtener la información del menú
    $sql = "SELECT * FROM menus WHERE id=$id";
    $result = $conn->query($sql);

    if ($result->num_rows != 1) {
        die("Menú no encontrado.");
    }

    $menu = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Editar Menú - Viandas</title>
</head>
<body>
    <div class="header">
        <h1>Editar Menú</h1>
        <a href="../php/logout.php">Logout</a>
    </div>
    <div class="container">
        <form action="edit_menu.php?id=<?php echo $id; ?>" method="POST">
            <div class="input-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo $menu['nombre']; ?>" required>
            </div>
            <div class="input-group">
                <label for="precio">Precio:</label>
                <input type="number" step="0.01" id="precio" name="precio" value="<?php echo $menu['precio']; ?>" required>
            </div>
            <div class="input-group">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo $menu['fecha']; ?>" required>
            </div>
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
