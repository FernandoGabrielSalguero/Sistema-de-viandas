<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: ../index.php");
    exit();
}

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Obtener los colegios y cursos para los filtros
$stmt = $pdo->prepare("SELECT Id, Nombre FROM Colegios");
$stmt->execute();
$colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT Id, Nombre FROM Cursos");
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener los filtros
$colegio_id = isset($_GET['colegio_id']) ? $_GET['colegio_id'] : '';
$curso_id = isset($_GET['curso_id']) ? $_GET['curso_id'] : '';
$fecha_entrega = isset($_GET['fecha_entrega']) ? $_GET['fecha_entrega'] : '';

// Construir la consulta SQL para obtener los pedidos con los filtros aplicados
$query = "SELECT c.Nombre as Colegio, cu.Nombre as Curso, COUNT(pc.Id) as CantidadPedidos
          FROM Pedidos_Comida pc
          JOIN Hijos h ON pc.Hijo_Id = h.Id
          JOIN Colegios c ON h.Colegio_Id = c.Id
          JOIN Cursos cu ON h.Curso_Id = cu.Id
          WHERE 1=1";

$params = [];

if ($colegio_id) {
    $query .= " AND c.Id = ?";
    $params[] = $colegio_id;
}

if ($curso_id) {
    $query .= " AND cu.Id = ?";
    $params[] = $curso_id;
}

if ($fecha_entrega) {
    $query .= " AND pc.Fecha_entrega = ?";
    $params[] = $fecha_entrega;
}

$query .= " GROUP BY c.Nombre, cu.Nombre";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pedidosPorEscuelaCurso = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para las tarjetas KPI
$kpi_query = "SELECT m.Nombre as NombreMenu, pc.Fecha_entrega, COUNT(pc.Id) AS CantidadPedidos
              FROM Pedidos_Comida pc
              JOIN Menú m ON pc.Menú_Id = m.Id
              WHERE 1=1";

$kpi_params = [];

if ($fecha_entrega) {
    $kpi_query .= " AND pc.Fecha_entrega = ?";
    $kpi_params[] = $fecha_entrega;
}

$kpi_query .= " GROUP BY m.Nombre, pc.Fecha_entrega";
$kpi_stmt = $pdo->prepare($kpi_query);
$kpi_stmt->execute($kpi_params);
$kpi_data = $kpi_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pedidos</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .filter-form {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .filter-form select,
        .filter-form input {
            width: 30%;
        }
        .kpi-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .kpi-card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 10px;
            width: 200px;
        }
        .kpi-card h2 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        .kpi-card p {
            margin: 5px 0 0;
            font-size: 16px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>Gestión de Pedidos</h1>

    <div class="filter-form">
        <select id="colegio_id" name="colegio_id" onchange="applyFilter()">
            <option value="">Todos los colegios</option>
            <?php foreach ($colegios as $colegio) : ?>
                <option value="<?php echo htmlspecialchars($colegio['Id']); ?>" <?php echo ($colegio_id == $colegio['Id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($colegio['Nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select id="curso_id" name="curso_id" onchange="applyFilter()">
            <option value="">Todos los cursos</option>
            <?php foreach ($cursos as $curso) : ?>
                <option value="<?php echo htmlspecialchars($curso['Id']); ?>" <?php echo ($curso_id == $curso['Id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($curso['Nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($fecha_entrega); ?>" onchange="applyFilter()">
    </div>

    <div class="kpi-container">
        <?php foreach ($kpi_data as $kpi) : ?>
            <div class="kpi-card">
                <h2><?php echo htmlspecialchars($kpi['NombreMenu']); ?></h2>
                <p><?php echo htmlspecialchars($kpi['CantidadPedidos']); ?> pedidos</p>
                <p>Fecha: <?php echo htmlspecialchars($kpi['Fecha_entrega']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Colegio</th>
                <th>Curso</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidosPorEscuelaCurso as $pedido) : ?>
            <tr>
                <td><?php echo htmlspecialchars($pedido['Colegio']); ?></td>
                <td><?php echo htmlspecialchars($pedido['Curso']); ?></td>
                <td><?php echo htmlspecialchars($pedido['CantidadPedidos']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        function applyFilter() {
            const colegioId = document.getElementById('colegio_id').value;
            const cursoId = document.getElementById('curso_id').value;
            const fechaEntrega = document.getElementById('fecha_entrega').value;

            let url = 'gestion_pedidos.php?';
            if (colegioId) url += 'colegio_id=' + colegioId + '&';
            if (cursoId) url += 'curso_id=' + cursoId + '&';
            if (fechaEntrega) url += 'fecha_entrega=' + fechaEntrega;

            window.location.href = url;
        }
    </script>
</body>
</html>
