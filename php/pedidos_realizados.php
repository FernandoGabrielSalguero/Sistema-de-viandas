<?php
include 'db.php'; // Asegúrate de que esta ruta es correcta y que el archivo db.php puede ser encontrado.

session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION['userid'];
$pedidos = [];

$sql = "SELECT * FROM pedidos WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    trigger_error('Error en la consulta: ' . $conn->error, E_USER_ERROR);
}
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
} else {
    echo "<p>No se encontraron pedidos.</p>"; // Solo para depuración, puede comentarse luego.
}
$stmt->close();