<?php
include 'db.php'; // Asegúrate de que la ruta es correcta para incluir el archivo de conexión a la base de datos

session_start();

// Verificar si el usuario ha iniciado sesión y tiene el rol adecuado
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Usuario') {
    echo "<script>alert('No estás autorizado para ver esta página.'); window.location.href='login.php';</script>";
    exit();
}

$userid = $_SESSION['userid'];

// Consulta para obtener los pedidos realizados por el usuario
$sql = "SELECT p.id, h.nombre AS hijo_nombre, h.apellido AS hijo_apellido, 
               m.nombre AS menu_nombre, m.fecha, p.estado
        FROM pedidos p
        JOIN hijos h ON p.hijo_id = h.id
        JOIN menus m ON p.menu_id = m.id
        WHERE p.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

// Comprobar si se obtuvieron resultados
if ($result->num_rows === 0) {
    echo "No hay pedidos realizados.";
} else {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Realizados</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Asegúrate de que la ruta es correcta -->
</head>
<body>
    <h1>Pedidos Realizados</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Hijo</th>
                <th>Menú</th>
                <th>Fecha</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['hijo_nombre'] . ' ' . $row['hijo_apellido']); ?></td>
                <td><?php echo htmlspecialchars($row['menu_nombre']); ?></td>
                <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                <td><?php echo htmlspecialchars($row['estado']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
<?php
}
$conn->close();
?>
