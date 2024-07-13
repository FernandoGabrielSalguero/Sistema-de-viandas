<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Obtener todas las tablas en la base de datos
$tablesQuery = $pdo->query("SHOW TABLES");
$tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);

echo "<h1>Tablas y sus Relaciones de Claves Foráneas</h1>";

foreach ($tables as $table) {
    echo "<h2>Tabla: $table</h2>";
    
    // Obtener la estructura de la tabla
    $structureQuery = $pdo->query("DESCRIBE $table");
    $structure = $structureQuery->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Defecto</th><th>Extra</th></tr>";
    foreach ($structure as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Obtener las claves foráneas de la tabla
    $foreignKeysQuery = $pdo->query("
        SELECT 
            COLUMN_NAME, 
            REFERENCED_TABLE_NAME, 
            REFERENCED_COLUMN_NAME 
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            TABLE_NAME = '$table' AND 
            CONSTRAINT_SCHEMA = DATABASE() AND 
            REFERENCED_TABLE_NAME IS NOT NULL;
    ");
    $foreignKeys = $foreignKeysQuery->fetchAll(PDO::FETCH_ASSOC);
    
    if ($foreignKeys) {
        echo "<h3>Claves Foráneas</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Columna</th><th>Tabla Referenciada</th><th>Columna Referenciada</th></tr>";
        foreach ($foreignKeys as $foreignKey) {
            echo "<tr>";
            echo "<td>{$foreignKey['COLUMN_NAME']}</td>";
            echo "<td>{$foreignKey['REFERENCED_TABLE_NAME']}</td>";
            echo "<td>{$foreignKey['REFERENCED_COLUMN_NAME']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No tiene claves foráneas.</p>";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Claves Foráneas</title>
</head>
<body>
</body>
</html>
