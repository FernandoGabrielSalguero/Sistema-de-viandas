<?php
// Conexión a la base de datos
include '../includes/header_admin.php';
include '../includes/db.php';

// Gestión de precios_hyt
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_precio'])) {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];

    // Insertar en la tabla precios_hyt
    $query = "INSERT INTO precios_hyt (nombre, precio, en_venta) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    if ($stmt->execute([$nombre, $precio, true])) {  // Por defecto, estará en venta
        $success = "Precio guardado correctamente.";
    } else {
        $error = "Error al guardar el precio.";
    }
}

// Actualizar estado de "En venta"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_venta'])) {
    $id = $_POST['id'];
    $en_venta = $_POST['en_venta'] == 'true' ? 1 : 0; // Convertir el valor recibido en booleano
    
    // Actualizar el estado en la base de datos
    $stmt = $pdo->prepare("UPDATE precios_hyt SET en_venta = ? WHERE id = ?");
    if ($stmt->execute([$en_venta, $id])) {
        $success = "Estado de venta actualizado.";
    } else {
        $error = "Error al actualizar el estado de venta.";
    }
}

// Obtener los precios actuales
$result = $pdo->query("SELECT * FROM precios_hyt");
$precios = $result->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Precios y Destinos - hyt_variables.php</title>
    <link rel="stylesheet" href="../css/hyt_variables.css">
    <script>
        function toggleVenta(id, enVenta) {
            // Actualizar el estado de "en venta" vía un formulario oculto
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'hyt_variables.php';

            var inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'id';
            inputId.value = id;
            form.appendChild(inputId);

            var inputVenta = document.createElement('input');
            inputVenta.type = 'hidden';
            inputVenta.name = 'en_venta';
            inputVenta.value = enVenta;
            form.appendChild(inputVenta);

            var inputSubmit = document.createElement('input');
            inputSubmit.type = 'hidden';
            inputSubmit.name = 'actualizar_venta';
            inputSubmit.value = '1';
            form.appendChild(inputSubmit);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</head>
<body>

<h1>Gestión de Precios de Viandas</h1>
<?php
if (isset($success)) {
    echo "<p class='success'>$success</p>";
}
if (isset($error)) {
    echo "<p class='error'>$error</p>";
}
?>

<form method="POST" action="hyt_variables.php">
    <label for="nombre">Nombre de la vianda:</label>
    <input type="text" id="nombre" name="nombre" placeholder="Ej: Almuerzo vegetariano" required>

    <label for="precio">Precio:</label>
    <input type="number" step="0.01" id="precio" name="precio" required>

    <button type="submit" name="guardar_precio">Guardar Precio</button>
</form>

<h2>Precios actuales</h2>
<table border="1">
    <tr>
        <th>Nombre</th>
        <th>Precio</th>
        <th>¿En venta?</th>
    </tr>
    <?php foreach ($precios as $precio): ?>
        <tr>
            <td><?php echo htmlspecialchars($precio['nombre']); ?></td>
            <td><?php echo htmlspecialchars($precio['precio']); ?></td>
            <td>
                <label class="switch">
                    <input type="checkbox" <?php echo $precio['en_venta'] ? 'checked' : ''; ?>
                           onchange="toggleVenta(<?php echo $precio['id']; ?>, this.checked)">
                    <span class="slider round"></span>
                </label>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Gestión de Destinos</h2>
<form method="POST" action="hyt_variables.php">
    <label for="nombre_destino">Nombre del destino:</label>
    <input type="text" id="nombre_destino" name="nombre_destino" required>

    <button type="submit" name="guardar_destino">Guardar Destino</button>
</form>

</body>
</html>
