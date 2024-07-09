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

// Obtener la cantidad de saldo aprobado
$stmt = $pdo->prepare("SELECT SUM(Saldo) as SaldoAprobado FROM Pedidos_Saldo WHERE Estado = 'Aprobado'");
$stmt->execute();
$saldoAprobado = $stmt->fetch(PDO::FETCH_ASSOC)['SaldoAprobado'] ?? 0.0;

// Obtener la cantidad de saldo en estado pendiente de aprobación
$stmt = $pdo->prepare("SELECT SUM(Saldo) as SaldoPendiente FROM Pedidos_Saldo WHERE Estado = 'Pendiente de aprobación'");
$stmt->execute();
$saldoPendiente = $stmt->fetch(PDO::FETCH_ASSOC)['SaldoPendiente'] ?? 0.0;

// Obtener la cantidad de pedidos por escuela y por curso con filtros
$query = "SELECT c.Nombre as Colegio, cu.Nombre as Curso, COUNT(pc.Id) as CantidadPedidos
          FROM Pedidos_Comida pc
          JOIN Hijos h ON pc.Hijo_Id = h.Id
          JOIN Colegios c ON h.Colegio_Id = c.Id
          JOIN Cursos cu ON h.Curso_Id = cu.Id";
$params = [];

$colegio_id = isset($_GET['colegio_id']) ? $_GET['colegio_id'] : '';
$curso_id = isset($_GET['curso_id']) ? $_GET['curso_id'] : '';

if ($colegio_id) {
    $query .= " WHERE c.Id = ?";
    $params[] = $colegio_id;
}

if ($curso_id) {
    $query .= $colegio_id ? " AND cu.Id = ?" : " WHERE cu.Id = ?";
    $params[] = $curso_id;
}

$query .= " GROUP BY c.Nombre, cu.Nombre";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pedidosPorEscuelaCurso = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la cantidad de usuarios registrados
$stmt = $pdo->prepare("SELECT COUNT(*) as CantidadUsuarios FROM Usuarios");
$stmt->execute();
$cantidadUsuarios = $stmt->fetch(PDO::FETCH_ASSOC)['CantidadUsuarios'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .kpi-card {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 10px;
            width: 200px;
            display: inline-block;
            vertical-align: top;
        }
        .kpi-card h2 {
            margin: 0;
            font-size: 24px;
            color: #007bff;
        }
        .kpi-card p {
            margin: 5px 0 0;
            font-size: 18px;
            color: #333;
        }
        .kpi-container {
            text-align: center;
        }
        .filter-form {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Dashboard Administrador</h1>
    <div class="filter-form">
        <form method="GET" action="dashboard.php">
            <label for="colegio_id">Filtrar por Colegio:</label>
            <select id="colegio_id" name="colegio_id">
                <option value="">Todos</option>
                <?php foreach ($colegios as $colegio) : ?>
                    <option value="<?php echo htmlspecialchars($colegio['Id']); ?>" <?php echo ($colegio_id == $colegio['Id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($colegio['Nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="curso_id">Filtrar por Curso:</label>
            <select id="curso_id" name="curso_id">
                <option value="">Todos</option>
                <?php foreach ($cursos as $curso) : ?>
                    <option value="<?php echo htmlspecialchars($curso['Id']); ?>" <?php echo ($curso_id == $curso['Id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($curso['Nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filtrar</button>
        </form>
    </div>
    <div class="kpi-container">
        <div class="kpi-card">
            <h2>Saldo Aprobado</h2>
            <p><?php echo number_format((float)$saldoAprobado, 2); ?> ARS</p>
        </div>
        <div class="kpi-card">
            <h2>Saldo Pendiente</h2>
            <p><?php echo number_format((float)$saldoPendiente, 2); ?> ARS</p>
        </div>
        <div class="kpi-card">
            <h2>Usuarios Registrados</h2>
            <p><?php echo $cantidadUsuarios; ?></p>
        </div>
    </div>
    <h2>Pedidos por Escuela y Curso</h2>
    <table>
        <tr>
            <th>Colegio</th>
            <th>Curso</th>
            <th>Cantidad de Pedidos</th>
        </tr>
        <?php foreach ($pedidosPorEscuelaCurso as $pedido) : ?>
        <tr>
            <td><?php echo htmlspecialchars($pedido['Colegio']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Curso']); ?></td>
            <td><?php echo htmlspecialchars($pedido['CantidadPedidos']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
