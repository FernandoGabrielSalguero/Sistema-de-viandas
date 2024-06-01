<?php
include 'db.php';

if (!isset($_GET['id'])) {
    die("ID del pedido no especificado.");
}

$id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $estado = $_POST['estado'];

    // Actualizar pedido
    $sql = "UPDATE pedidos SET estado='$estado' WHERE id=$id";
    $conn->query($sql);

    header("Location: ../views/manage_orders.php");
    exit();
} else {
    // Obtener la información del pedido
    $sql = "SELECT * FROM pedidos WHERE id=$id";
    $result = $conn->query($sql);

    if ($result->num_rows != 1) {
        die("Pedido no encontrado.");
    }

    $pedido = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Editar Pedido - Viandas</title>
</head>
<body>
    <div class="header">
        <h1>Editar Pedido</h1>
        <a href="../php/logout.php">Logout</a>
    </div>
    <div class="container">
        <form action="edit_order.php?id=<?php echo $id; ?>" method="POST">
            <div class="input-group">
                <label for="estado">Estado:</label>
                <select id="estado" name="estado" required>
                    <option value="En espera de aprobación" <?php echo $pedido['estado'] == 'En espera de aprobación' ? 'selected' : ''; ?>>En espera de aprobación</option>
                    <option value="Aprobado" <?php echo $pedido['estado'] == 'Aprobado' ? 'selected' : ''; ?>>Aprobado</option>
                    <option value="Cancelado" <?php echo $pedido['estado'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                    <option value="Procesando" <?php echo $pedido['estado'] == 'Procesando' ? 'selected' : ''; ?>>Procesando</option>
                </select>
            </div>
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
