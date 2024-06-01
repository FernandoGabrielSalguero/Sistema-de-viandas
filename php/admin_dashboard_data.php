<?php
include 'db.php';

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

$data = [];

// Condiciones para la fecha
$dateCondition = "";
if ($startDate && $endDate) {
    $dateCondition = "AND pedidos.fecha_pedido BETWEEN '$startDate' AND '$endDate'";
}

// Total de dinero en pedidos aprobados
$sql = "SELECT SUM(menus.precio) AS total_aprobado FROM pedidos JOIN menus ON pedidos.menu_id = menus.id WHERE pedidos.estado = 'Aprobado' $dateCondition";
$result = $conn->query($sql);
$data['total_aprobado'] = $result->fetch_assoc()['total_aprobado'] ?? 0;

// Total de dinero en pedidos a la espera
$sql = "SELECT SUM(menus.precio) AS total_espera FROM pedidos JOIN menus ON pedidos.menu_id = menus.id WHERE pedidos.estado = 'En espera de aprobación' $dateCondition";
$result = $conn->query($sql);
$data['total_espera'] = $result->fetch_assoc()['total_espera'] ?? 0;

// Total de viandas pedidas
$sql = "SELECT COUNT(*) AS total_viandas FROM pedidos WHERE 1=1 $dateCondition";
$result = $conn->query($sql);
$data['total_viandas'] = $result->fetch_assoc()['total_viandas'] ?? 0;

// Total de usuarios
$sql = "SELECT COUNT(*) AS total_usuarios FROM usuarios";
$result = $conn->query($sql);
$data['total_usuarios'] = $result->fetch_assoc()['total_usuarios'] ?? 0;

// Usuarios que hicieron pedidos
$sql = "SELECT COUNT(DISTINCT usuario_id) AS usuarios_con_pedidos FROM pedidos WHERE 1=1 $dateCondition";
$result = $conn->query($sql);
$data['usuarios_con_pedidos'] = $result->fetch_assoc()['usuarios_con_pedidos'] ?? 0;

// Usuarios que no hicieron pedidos
$data['usuarios_sin_pedidos'] = $data['total_usuarios'] - $data['usuarios_con_pedidos'];

// Pedidos por usuario
$sql = "SELECT usuario_id, COUNT(*) AS pedidos_realizados FROM pedidos WHERE 1=1 $dateCondition GROUP BY usuario_id";
$result = $conn->query($sql);
$data['pedidos_por_usuario'] = $result->fetch_all(MYSQLI_ASSOC);

// Devoluciones (suponiendo que el estado 'Cancelado' representa una devolución)
$sql = "SELECT COUNT(*) AS total_devoluciones FROM pedidos WHERE estado = 'Cancelado' $dateCondition";
$result = $conn->query($sql);
$data['total_devoluciones'] = $result->fetch_assoc()['total_devoluciones'] ?? 0;

echo json_encode($data);