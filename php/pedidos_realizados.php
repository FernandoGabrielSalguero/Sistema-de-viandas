<?php
include 'db.php';  // Asegúrate de que la inclusión del archivo de conexión a la base de datos es correcta.

session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php"); // Redireccionar al login si no está logueado
    exit();
}

$userid = $_SESSION['userid'];
$pedidos = [];

$sql = "SELECT pedidos.id, usuarios.usuario, hijos.nombre AS hijo_nombre, hijos.apellido AS hijo_apellido, 
                menus.nombre AS menu_nombre, pedidos.estado, pedidos.fecha_pedido
        FROM pedidos
        JOIN usuarios ON pedidos.usuario_id = usuarios.id
        JOIN hijos ON pedidos.hijo_id = hijos.id
        JOIN menus ON pedidos.menu_id = menus.id
        WHERE usuarios.id = ?";  // Usar el ID del usuario para filtrar los pedidos

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    trigger_error('Error al preparar la consulta: ' . $conn->error, E_USER_ERROR);
}

$stmt->bind_param("i", $userid);  // Vincular el ID del usuario a la consulta
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
} else {
    echo "<p>No se encontraron pedidos.</p>";
}

$stmt->close();
