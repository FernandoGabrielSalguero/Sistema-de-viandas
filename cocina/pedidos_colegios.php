<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_cocina.php';
include '../includes/db.php';

// Obtener la cantidad total de viandas por menú y sus preferencias alimenticias
$query_menus = "
    SELECT m.Nombre AS MenuNombre, COUNT(*) AS Cantidad, pc.Fecha_entrega 
    FROM Pedidos_Comida pc
    JOIN Menú m ON pc.Menú_Id = m.Id
    WHERE pc.Estado = 'Procesando'
    GROUP BY m.Nombre, pc.Fecha_entrega
";
$stmt = $pdo->prepare($query_menus);
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener alumnos con preferencias alimenticias
$query_preferencias = "
    SELECT h.Nombre AS Alumno, m.Nombre AS MenuNombre, pa.Nombre AS Preferencia
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
    JOIN Preferencias_Alimenticias pa ON pc.Preferencias_alimenticias = pa.Id
    WHERE pc.Estado = 'Procesando' AND pc.Preferencias_alimenticias IS NOT NULL
";
$stmt = $pdo->prepare($query_preferencias);
$stmt->execute();
$preferencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar preferencias por menú
$preferencias_por_menu = [];
foreach ($preferencias as $pref) {
    $menu = $pref['MenuNombre'];
    if (!isset($preferencias_por_menu[$menu])) {
        $preferencias_por_menu[$menu] = [];
    }
    $preferencias_por_menu[$menu][] = $pref;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Cocina</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .card {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            width: 250px;
            text-align: center;
            background-color: #f8f8f8;
        }
        .warning {
            background-color: #ffeb3b;
        }
        .danger {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Dashboard Cocina</h1>
    <h2>Total de Menús</h2>
    <div class="card-container">
        <?php foreach ($menus as $menu) : ?>
            <?php 
            $menuNombre = htmlspecialchars($menu['MenuNombre']);
            $cantidad = htmlspecialchars($menu['Cantidad']);
            $fechaEntrega = htmlspecialchars($menu['Fecha_entrega']);
            $prefCount = isset($preferencias_por_menu[$menuNombre]) ? count($preferencias_por_menu[$menuNombre]) : 0;
            $cardClass = $prefCount > 0 ? ($prefCount > 2 ? 'danger' : 'warning') : '';
            ?>
            <div class="card <?php echo $cardClass; ?>">
                <h3><?php echo $menuNombre; ?></h3>
                <p><strong>Cantidad:</strong> <?php echo $cantidad; ?></p>
                <p><strong>Fecha de entrega:</strong> <?php echo $fechaEntrega; ?></p>
                <?php if ($prefCount > 0) : ?>
                    <p><strong>⚠ <?php echo $prefCount; ?> alumno(s) con preferencias alimenticias</strong></p>
                    <ul>
                        <?php foreach ($preferencias_por_menu[$menuNombre] as $pref) : ?>
                            <li><?php echo htmlspecialchars($pref['Alumno'] . ' - ' . $pref['Preferencia']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
