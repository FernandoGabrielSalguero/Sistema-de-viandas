<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Obtener la cantidad de saldo aprobado
$stmt = $pdo->prepare("SELECT SUM(Saldo) as SaldoAprobado FROM Pedidos_Saldo WHERE Estado = 'Aprobado'");
$stmt->execute();
$saldoAprobado = $stmt->fetch(PDO::FETCH_ASSOC)['SaldoAprobado'];

// Obtener la cantidad de saldo en estado pendiente de aprobación
$stmt = $pdo->prepare("SELECT SUM(Saldo) as SaldoPendiente FROM Pedidos_Saldo WHERE Estado = 'Pendiente de aprobación'");
$stmt->execute();
$saldoPendiente = $stmt->fetch(PDO::FETCH_ASSOC)['SaldoPendiente'];

// Obtener la cantidad de pedidos por escuela y por curso
$stmt = $pdo->prepare("SELECT c.Nombre as Colegio, cu.Nombre as Curso, COUNT(pc.Id) as CantidadPedidos
                       FROM Pedidos_Comida pc
                       JOIN Hijos h ON pc.Hijo_Id = h.Id
                       JOIN Colegios c ON h.Colegio_Id = c.Id
                       JOIN Cursos cu ON h.Curso_Id = cu.Id
                       GROUP BY c.Nombre, cu.Nombre");
$stmt->execute();
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
    </style>
</head>
<body>
    <h1>Dashboard Administrador</h1>
    <div class="kpi-container">
        <div class="kpi-card">
            <h2>Saldo Aprobado</h2>
            <p><?php echo number_format($saldoAprobado, 2); ?> ARS</p>
        </div>
        <div class="kpi-card">
            <h2>Saldo Pendiente</h2>
            <p><?php echo number_format($saldoPendiente, 2); ?> ARS</p>
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
