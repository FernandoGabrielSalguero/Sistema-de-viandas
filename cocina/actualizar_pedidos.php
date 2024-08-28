<?php
include '../includes/db.php';

// Procesar el formulario de filtro por fecha de entrega y colegio
$fecha_filtro = $_POST['fecha_entrega'] ?? '';
$colegio_filtro = $_POST['colegio'] ?? '';

// Obtener la cantidad total de viandas compradas, agrupadas por nombre de menú y día de entrega
$query_menus = "
    SELECT m.Nombre AS MenuNombre, DATE_FORMAT(pc.Fecha_entrega, '%d/%m/%y') AS FechaEntrega, COUNT(*) AS Cantidad
    FROM Pedidos_Comida pc
    JOIN Menú m ON pc.Menú_Id = m.Id
    WHERE pc.Estado = 'Procesando'";  // Filtro base por Estado

// Inicializar arreglo de parámetros
$params = [];

// Aplicar filtros adicionales si están presentes
if (!empty($fecha_filtro)) {
    $query_menus .= " AND pc.Fecha_entrega = ? ";
    $params[] = $fecha_filtro;
}

if (!empty($colegio_filtro)) {
    $query_menus .= "
        AND pc.Hijo_Id IN (
            SELECT h.Id FROM Hijos h WHERE h.Colegio_Id = ?
        )
    ";
    $params[] = $colegio_filtro;
}

$query_menus .= " GROUP BY m.Nombre, pc.Fecha_entrega";

$stmt = $pdo->prepare($query_menus);
$stmt->execute($params);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver los datos en formato JSON
echo json_encode($menus);
