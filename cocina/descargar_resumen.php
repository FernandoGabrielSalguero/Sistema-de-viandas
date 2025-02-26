<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';

// Obtener los filtros de fecha y colegio
$fecha_filtro = isset($_POST['fecha_filtro']) ? $_POST['fecha_filtro'] : '';
$colegio_filtro = isset($_POST['colegio_filtro']) ? $_POST['colegio_filtro'] : '';

// Consulta para obtener los datos
$query = "
    SELECT h.Nombre AS Alumno, cu.Nombre AS Curso, m.Nombre AS Menu, 
           pc.Fecha_entrega, pc.Estado, c.Nombre AS Colegio
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Colegios c ON h.Colegio_Id = c.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
    WHERE pc.Estado = 'Procesando'
";

// Aplicar filtros si existen
$params = [];
if (!empty($fecha_filtro)) {
    $query .= " AND pc.Fecha_entrega = ?";
    $params[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query .= " AND c.Nombre = ?";
    $params[] = $colegio_filtro;
}

$query .= " ORDER BY pc.Fecha_entrega ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear el archivo CSV
$filename = "resumen_pedidos_" . date("Y-m-d") . ".csv";
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename");

$output = fopen("php://output", "w");
fputs($output, "\xEF\xBB\xBF"); // Para evitar problemas con acentos

// Escribir encabezados
fputcsv($output, ["Alumno", "Curso", "Menú", "Fecha de Entrega", "Estado", "Colegio"]);

// Escribir datos
foreach ($pedidos as $pedido) {
    fputcsv($output, $pedido);
}

fclose($output);
exit();
