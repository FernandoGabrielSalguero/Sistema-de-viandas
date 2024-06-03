<?php
include 'db.php';

session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION['userid'];
$pedidos = [];

$sql = "SELECT * FROM pedidos WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
} else {
    echo "<p>No se encontraron pedidos.</p>"; // Agregar esta línea para depuración
}
$stmt->close();
