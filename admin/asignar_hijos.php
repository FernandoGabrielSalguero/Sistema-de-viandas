<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Obtener la lista de hijos asignados a cada usuario (simplificado)
$query = "
    SELECT uh.Usuario_Id, uh.Hijo_Id, u.Nombre AS NombrePapa
    FROM Usuarios_Hijos uh
    JOIN Usuarios u ON uh.Usuario_Id = u.Id
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Depuración de datos
echo "<pre>";
echo "Query: $query\n";
echo "Asignaciones:\n";
var_dump($asignaciones);
echo "</pre>";
exit();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Hijos a Papás</title>
</head>
<body>
    <h1>Asignar Hijos a Papás</h1>
</body>
</html>
