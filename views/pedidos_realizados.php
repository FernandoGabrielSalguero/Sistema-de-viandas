<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Pedidos Realizados</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            color: #333;
        }

        .header {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 8px;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Estos son tus pedidos, <?php echo htmlspecialchars($userInfo['nombre']); ?>!</h1>
        <p>Saldo: $<?php echo number_format($saldo, 2); ?></p>
        <button onclick="location.href='user_dashboard.php'">Inicio</button>
        <button onclick="window.open('https://wa.me/543406173', '_blank')">Contacto</button>
        <button onclick="location.href='../php/logout.php'">Salir</button>
    </div>
    <div style="padding: 0 20px;">
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
                ?>
            </tbody>
        </table>
    </div>
    <script>
        function uploadFile(orderId) {
            // Implementar la función de subir archivo aquí.
            console.log("Subiendo archivo para el pedido: " + orderId);
        }

        function cancelOrder(orderId) {
            if (confirm('¿Está seguro que desea cancelar este pedido?')) {
                // Implementar la llamada a la API o redirección para cancelar el pedido aquí.
                console.log("Pedido " + orderId + " cancelado.");
            }
        }
    </script>
</body>
</html>
