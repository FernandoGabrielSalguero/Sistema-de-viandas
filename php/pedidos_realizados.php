<?php
include 'db.php'; // Asegúrate de tener un archivo db.php que maneje la conexión a la base de datos

session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php"); // Redirección al login si el usuario no está logueado
    exit();
}

$userid = $_SESSION['userid'];
$pedidos = [];

$sql = "SELECT * FROM pedidos WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pedidos[] = $row;
}
$stmt->close();
