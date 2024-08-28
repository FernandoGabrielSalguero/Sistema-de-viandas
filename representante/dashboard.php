<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'representante') {
    header("Location: ../index.php");
    exit();
}

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_representante.php';
include '../includes/db.php';

$usuario_id = $_SESSION['usuario_id'];

// Obtener el colegio asignado al representante
$stmt = $pdo->prepare("SELECT Colegio_Id FROM Representantes_Colegios WHERE Representante_Id = ?");
$stmt->execute([$usuario_id]);
$colegio_id = $stmt->fetchColumn();

// Obtener cursos del colegio
$stmt = $pdo->prepare("SELECT Id, Nombre FROM Cursos WHERE Colegio_Id = ?");
$stmt->execute([$colegio_id]);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inicializar variables de filtros
$filtro_curso = isset($_GET['curso_id']) ? $_GET['curso_id'] : '';
$filtro_fecha = isset($_GET['fecha_entrega']) ? $_GET['fecha_entrega'] : '';

// Obtener pedidos de viandas con filtros
$query = "
    SELECT 
        pc.Id AS Pedido_Id, 
        h.Nombre as Hijo, 
        m.Nombre as Menu, 
        pc.Fecha_entrega, 
        pc.Estado, 
        COALESCE(pa.Nombre, 'Sin preferencias alimenticias') as Preferencias_alimenticias
    FROM 
        Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN `Menú` m ON pc.Menú_Id = m.Id
    LEFT JOIN Preferencias_Alimenticias pa ON pc.Preferencias_alimenticias = pa.Id
    WHERE 
        h.Colegio_Id = :colegio_id
";

$params = ['colegio_id' => $colegio_id];

if ($filtro_curso) {
    $query .= " AND h.Curso_Id = :curso_id";
    $params['curso_id'] = $filtro_curso;
}

if ($filtro_fecha) {
    $query .= " AND pc.Fecha_entrega = :fecha_entrega";
    $params['fecha_entrega'] = $filtro_fecha;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pedidos_viandas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Representante</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .filter-item {
            flex: 1 1 calc(33.333% - 10px);
            min-width: 200px;
        }
        @media (max-width: 768px) {
            .filter-item {
                flex: 1 1 calc(50% - 10px);
            }
        }
        @media (max-width: 480px) {
            .filter-item {
                flex: 1 1 100%;
            }
        }
        .filters form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
    </style>
</head>
<body>
    <h1>Bienvenido, Representante</h1>

    <h2>Historial de Pedidos de Viandas</h2>
    <form method="get" action="dashboard.php" class="filters">
        <div class="filter-item">
            <label for="curso_id">Filtrar por Curso:</label>
            <select id="curso_id" name="curso_id">
                <option value="">Todos</option>
                <?php foreach ($cursos as $curso) : ?>
                    <option value="<?php echo $curso['Id']; ?>" <?php if ($filtro_curso == $curso['Id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($curso['Nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-item">
            <label for="fecha_entrega">Filtrar por Fecha de Entrega:</label>
            <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($filtro_fecha); ?>">
        </div>

        <div class="filter-item">
            <button type="submit">Filtrar</button>
        </div>
    </form>

    <table>
        <tr>
            <th>ID Pedido</th>
            <th>Hijo</th>
            <th>Menú</th>
            <th>Fecha de Entrega</th>
            <th>Estado</th>
            <th>Preferencias Alimenticias</th>
        </tr>
        <?php foreach ($pedidos_viandas as $pedido) : ?>
        <tr>
            <td><?php echo htmlspecialchars($pedido['Pedido_Id']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Hijo']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Menu']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Fecha_entrega'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pedido['Estado']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Preferencias_alimenticias']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
