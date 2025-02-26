<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';

$nivel = isset($_GET['nivel']) ? $_GET['nivel'] : '';

$query = "
    SELECT pc.Id AS id_pedido, h.Nombre AS hijo, cu.Nombre AS curso, m.Nombre AS menu,
           pc.Fecha_entrega, pc.Estado, pa.Nombre AS preferencias_alimenticias
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
    JOIN Preferencias_Alimenticias pa ON pc.Preferencias_alimenticias = pa.Id
    WHERE pc.Estado = 'Procesando' AND cu.Nivel_Educativo = ?
";

$stmt = $pdo->prepare($query);
$stmt->execute([$nivel]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver los datos en formato JSON
echo json_encode($resultado);
