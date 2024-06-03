
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Realizados</title>
</head>
<body>
    <h1>Pedidos Realizados</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Fecha</th>
            <th>Estado</th>
        </tr>
        <?php foreach ($pedidos as $pedido): ?>
        <tr>
            <td><?php echo htmlspecialchars($pedido['id']); ?></td>
            <td><?php echo htmlspecialchars($pedido['nombre']); ?></td>
            <td><?php echo htmlspecialchars($pedido['fecha']); ?></td>
            <td><?php echo htmlspecialchars($pedido['estado']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
