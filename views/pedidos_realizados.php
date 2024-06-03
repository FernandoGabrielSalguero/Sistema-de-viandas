<?php
include '../php/db.php'; // Ajusta esta línea según la ubicación real de db.php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 'Usuario') {
    echo "<script>console.error('Usuario no autorizado o sesión no iniciada');</script>";
    header("Location: login.php");
    exit();
}

$userid = $_SESSION['userid'];
$userQuery = "SELECT nombre FROM usuarios WHERE id = $userid";
$userInfo = $conn->query($userQuery)->fetch_assoc();

$sql = "SELECT h.*, c.nombre as colegio_nombre, cu.nombre as curso_nombre FROM hijos h
        JOIN colegios c ON h.colegio_id = c.id
        JOIN cursos cu ON h.curso_id = cu.id
        WHERE h.usuario_id = $userid";
$hijos = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

$saldo_result = $conn->query("SELECT saldo FROM usuarios WHERE id = $userid");
$saldo = $saldo_result->num_rows > 0 ? $saldo_result->fetch_assoc()['saldo'] : 0;

$menus_result = $conn->query("SELECT * FROM menus ORDER BY fecha ASC");
$menus = [];
if ($menus_result->num_rows > 0) {
    while ($row = $menus_result->fetch_assoc()) {
        $menus[$row['fecha']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css"> <!-- Asegúrate que la ruta al CSS es correcta -->
    <title>Pedidos Realizados</title>
</head>
<body>
    <div class="header">
        <h1>Pedidos Realizados</h1>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Hijo</th>
                <th>Menú</th>
                <th>Fecha de Pedido</th>
                <th>Estado</th>
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
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay pedidos realizados</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
<?php $conn->close(); ?>
