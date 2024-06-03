<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Pedidos Realizados</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header {
            background-color: #f9f9f9;
            padding: 10px 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: auto;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        button:hover {
            background-color: #45a049;
        }
        .container {
            width: 95%;
            margin: 0 auto; /* Center the table */
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Estos son tus pedidos, <?php echo htmlspecialchars($userInfo['nombre']); ?>!</h1>
        <p>Saldo: $<?php echo number_format($saldo, 2); ?></p>
        <button onclick="location.href='user_dashboard.php'">Inicio</button>
        <button style="background-color: #25d366;" onclick="window.location.href='https://wa.me/543406173';">Contacto</button>
        <button onclick="location.href='../php/logout.php'">Cerrar sesión</button>
    </div>
    <div class="container">
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
                $stmt = $conn->prepare("SELECT pedidos.id, hijos.nombre AS hijo_nombre, hijos.apellido AS hijo_apellido,
                                        menus.nombre AS menu_nombre, menus.fecha, pedidos.estado
                                        FROM pedidos
                                        JOIN hijos ON pedidos.hijo_id = hijos.id
                                        JOIN menus ON pedidos.menu_id = menus.id
                                        WHERE pedidos.usuario_id = ?");
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
                        echo "<td><button onclick='uploadFile(" . $row['id'] . ")'>Subir Archivo</button><button onclick='cancelOrder(" . $row['id'] . ")'>Cancelar Pedido</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No hay pedidos realizados</td></tr>";
                }
                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>
    <script>
        function uploadFile(orderId) {
            alert("Función para subir archivo no implementada aún.");
        }

        function cancelOrder(orderId) {
            if (confirm('¿Está seguro que desea cancelar este pedido?')) {
                console.log("Pedido " + orderId + " cancelado.");
                // Implementar lógica para cancelar el pedido
            }
        }
    </script>
</body>
</html>
