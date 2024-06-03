<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css"> <!-- Asegúrate que la ruta al CSS es correcta -->
    <title>Pedidos Realizados</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .header { background-color: #f9f9f9; padding: 10px 20px; text-align: center; }
        button { padding: 8px 16px; margin-right: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        form { margin: 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pedidos Realizados</h1>
        <p>Saldo: $<?= number_format($saldo, 2); ?></p>
        <button onclick="location.href='../php/logout.php'">Cerrar sesión</button>
        <button onclick="window.open('https://wa.me/542613406173', '_blank')">Contacto</button>
    </div>
    <table>
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
            <?php
            $query = "SELECT pedidos.id, hijos.nombre AS hijo_nombre, hijos.apellido AS hijo_apellido,
                      menus.nombre AS menu_nombre, menus.fecha, pedidos.estado
                      FROM pedidos
                      JOIN hijos ON pedidos.hijo_id = hijos.id
                      JOIN menus ON pedidos.menu_id = menus.id
                      WHERE pedidos.usuario_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $userid);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['hijo_nombre'] . ' ' . $row['hijo_apellido']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['menu_nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['fecha']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                    echo "<td>";
                    echo "<form method='POST' action='../php/cancel_order.php'>";
                    echo "<input type='hidden' name='cancel' value='" . $row['id'] . "'>";
                    echo "<button type='submit'>Cancelar Pedido</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No hay pedidos realizados</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
