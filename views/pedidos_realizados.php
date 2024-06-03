<?php
include '../php/db.php';
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Usuario') {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION['userid'];
$userQuery = "SELECT nombre FROM usuarios WHERE id = $userid";
$userInfo = $conn->query($userQuery)->fetch_assoc();

$saldo_result = $conn->query("SELECT saldo FROM usuarios WHERE id = $userid");
$saldo = $saldo_result->num_rows > 0 ? $saldo_result->fetch_assoc()['saldo'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Pedidos Realizados</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .header { background-color: #f9f9f9; padding: 10px 20px; text-align: center; }
        button { padding: 8px 16px; margin-right: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Qué bueno verte de nuevo, <?php echo htmlspecialchars($userInfo['nombre']); ?>!</h1>
        <p>Saldo: $<?php echo number_format($saldo, 2); ?></p>
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
    <script>
        function uploadFile(orderId) {
            alert("Función para subir archivo no implementada aún.");
        }
        
        function cancelOrder(orderId) {
            if (confirm('¿Está seguro que desea cancelar este pedido?')) {
                // Implementar llamada a la API o redirección para cancelar pedido
                console.log("Pedido " + orderId + " cancelado.");
            }
        }
    </script>
</body>
</html>
