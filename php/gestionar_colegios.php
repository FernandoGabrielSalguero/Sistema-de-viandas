<?php
$servername = "127.0.0.1:3306";
$username = "u437094107_adm111n";
$password = "9t:RuQ7^nr+/";
$dbname = "u437094107_viandas_sch00l";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);

$action = $data['action'];

if ($action == "create") {
    $nombre = $data['nombre'];
    $stmt = $conn->prepare("INSERT INTO colegios (nombre) VALUES (?)");
    $stmt->bind_param("s", $nombre);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(["success" => $success]);
} elseif ($action == "read") {
    $result = $conn->query("SELECT id, nombre FROM colegios");
    $colegios = [];
    while ($row = $result->fetch_assoc()) {
        $colegios[] = $row;
    }
    echo json_encode(["colegios" => $colegios]);
} elseif ($action == "update") {
    $id = $data['id'];
    $nombre = $data['nombre'];
    $stmt = $conn->prepare("UPDATE colegios SET nombre = ? WHERE id = ?");
    $stmt->bind_param("si", $nombre, $id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(["success" => $success]);
} elseif ($action == "delete") {
    $id = $data['id'];
    $stmt = $conn->prepare("DELETE FROM colegios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(["success" => $success]);
}

$conn->close();
