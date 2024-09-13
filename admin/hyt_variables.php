<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php'; // Asegúrate de que el archivo db.php contiene la conexión PDO

// Verificar que la conexión a la base de datos esté establecida correctamente
// if (!$pdo) {
//     die("Error de conexión a la base de datos");
// } else {
//     echo "Conexión establecida correctamente";
// }

// Gestión de precios_hyt
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_precio'])) {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];

    // Insertar en la tabla precios_hyt usando PDO
    $query = "INSERT INTO precios_hyt (nombre, precio) VALUES (:nombre, :precio)";
    $stmt = $pdo->prepare($query);
    if ($stmt->execute(['nombre' => $nombre, 'precio' => $precio])) {
        echo "Precio guardado correctamente";
    } else {
        echo "Error al guardar el precio";
    }
}

// Gestión de destinos_hyt
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_destino'])) {
    $nombre_destino = $_POST['nombre_destino'];

    // Insertar en la tabla destinos_hyt usando PDO
    $query = "INSERT INTO destinos_hyt (nombre) VALUES (:nombre)";
    $stmt = $pdo->prepare($query);
    if ($stmt->execute(['nombre' => $nombre_destino])) {
        echo "Destino guardado correctamente";
    } else {
        echo "Error al guardar el destino";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Precios y Destinos</title>
    <link rel="stylesheet" href="../css/hyt_variables.css">
</head>
<body>

<h1>Gestión de Precios de Viandas</h1>
<form method="POST" action="hyt_variables.php">
    <label for="nombre">Tipo de vianda:</label>
    <select name="nombre" required>
        <option value="Desayuno dia siguiente">Desayuno día siguiente</option>
        <option value="Almuerzo caliente">Almuerzo caliente</option>
        <option value="Media tarde">Media tarde</option>
        <option value="Refrigerio sandwich almuerzo">Refrigerio sandwich almuerzo</option>
        <option value="Cena caliente">Cena caliente</option>
        <option value="Refrigerio sandwich cena">Refrigerio sandwich cena</option>
        <option value="Desayuno noche">Desayuno noche</option>
        <option value="Sandwich noche">Sandwich noche</option>
    </select>

    <label for="precio">Precio:</label>
    <input type="number" step="0.01" name="precio" required>

    <button type="submit" name="guardar_precio">Guardar Precio</button>
</form>

<h1>Gestión de Destinos</h1>
<form method="POST" action="hyt_variables.php">
    <label for="nombre_destino">Nombre del destino:</label>
    <input type="text" name="nombre_destino" required>

    <button type="submit" name="guardar_destino">Guardar Destino</button>
</form>

<!-- Sección para mostrar precios y destinos existentes -->
<h2>Precios actuales</h2>
<table border="1">
    <tr>
        <th>Nombre</th>
        <th>Precio</th>
    </tr>
    <?php
    // Obtener los precios actuales usando PDO
    $stmt = $pdo->query("SELECT * FROM precios_hyt");
    $precios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($precios as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($row['precio']) . "</td>";
        echo "</tr>";
    }
    ?>
</table>

<h2>Destinos actuales</h2>
<table border="1">
    <tr>
        <th>Nombre</th>
    </tr>
    <?php
    // Obtener los destinos actuales usando PDO
    $stmt = $pdo->query("SELECT * FROM destinos_hyt");
    $destinos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($destinos as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "</tr>";
    }
    ?>
</table>

</body>
</html>
