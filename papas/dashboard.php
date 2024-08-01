<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Establecer la zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');


session_start();
include '../includes/header_papas.php';
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'papas') {
    header("Location: ../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener información del usuario
$stmt = $pdo->prepare("SELECT Nombre, Correo, Saldo FROM Usuarios WHERE Id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Inicializar variables de filtros
$filtro_fecha_entrega = isset($_GET['fecha_entrega']) ? $_GET['fecha_entrega'] : '';
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_hijo = isset($_GET['hijo']) ? $_GET['hijo'] : '';
$filtro_menu = isset($_GET['menu']) ? $_GET['menu'] : '';

// Construir la consulta con filtros
$query_pedidos = "SELECT pc.Id, h.Nombre as Hijo, m.Nombre as Menú, DATE_FORMAT(m.Fecha_entrega, '%d/%b/%y') as Fecha_entrega, DATE_FORMAT(pc.Fecha_pedido, '%d/%b/%y %H:%i:%s') as Fecha_pedido, pc.Estado
                  FROM Pedidos_Comida pc
                  JOIN Hijos h ON pc.Hijo_Id = h.Id
                  JOIN `Menú` m ON pc.Menú_Id = m.Id
                  JOIN Usuarios_Hijos uh ON h.Id = uh.Hijo_Id
                  WHERE uh.Usuario_Id = :usuario_id";

$params = ['usuario_id' => $usuario_id];

if ($filtro_fecha_entrega) {
    $query_pedidos .= " AND m.Fecha_entrega = :fecha_entrega";
    $params['fecha_entrega'] = $filtro_fecha_entrega;
}
if ($filtro_estado) {
    $query_pedidos .= " AND pc.Estado = :estado";
    $params['estado'] = $filtro_estado;
}
if ($filtro_hijo) {
    $query_pedidos .= " AND h.Id = :hijo";
    $params['hijo'] = $filtro_hijo;
}
if ($filtro_menu) {
    $query_pedidos .= " AND m.Id = :menu";
    $params['menu'] = $filtro_menu;
}

$stmt = $pdo->prepare($query_pedidos);
$stmt->execute($params);
$pedidos_viandas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener historial de pedidos de saldo
$stmt = $pdo->prepare("SELECT Id, Saldo, Estado, Comprobante, DATE_FORMAT(Fecha_pedido, '%d/%b/%y %H:%i:%s') as Fecha_pedido FROM Pedidos_Saldo WHERE Usuario_Id = ?");
$stmt->execute([$usuario_id]);
$pedidos_saldo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener listas para los filtros
$stmt = $pdo->prepare("SELECT Id, Nombre FROM Hijos WHERE Id IN (SELECT Hijo_Id FROM Usuarios_Hijos WHERE Usuario_Id = ?)");
$stmt->execute([$usuario_id]);
$hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT Id, Nombre FROM `Menú` WHERE Estado = 'En venta'");
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Papás</title>
    <link rel="stylesheet" href="../css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .filter-item {
            flex: 1 1 calc(25% - 10px);
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
        .saldo {
            font-size: 24px;
            font-weight: bold;
            color: green;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($usuario['Nombre']); ?></h1>
    <p>Correo: <?php echo htmlspecialchars($usuario['Correo']); ?></p>
    <p class="saldo">Saldo disponible: <?php echo number_format($usuario['Saldo'], 2); ?> ARS</p>

    <?php
    if (isset($_GET['error'])) {
        echo "<p class='error'>" . htmlspecialchars($_GET['error']) . "</p>";
    }
    if (isset($_GET['success'])) {
        echo "<p class='success'>" . htmlspecialchars($_GET['success']) . "</p>";
    }
    ?>

    <h2>Historial de Pedidos de Viandas</h2>
    <form method="get" action="dashboard.php" class="filters">
        <div class="filter-item">
            <label for="fecha_entrega">Fecha de Entrega:</label>
            <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($filtro_fecha_entrega); ?>">
        </div>
        
        <div class="filter-item">
            <label for="estado">Estado:</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                <option value="Procesando" <?php if ($filtro_estado == 'Procesando') echo 'selected'; ?>>Procesando</option>
                <option value="Cancelado" <?php if ($filtro_estado == 'Cancelado') echo 'selected'; ?>>Cancelado</option>
            </select>
        </div>
        
        <div class="filter-item">
            <label for="hijo">Hijo:</label>
            <select id="hijo" name="hijo">
                <option value="">Todos</option>
                <?php foreach ($hijos as $hijo) : ?>
                    <option value="<?php echo $hijo['Id']; ?>" <?php if ($filtro_hijo == $hijo['Id']) echo 'selected'; ?>><?php echo htmlspecialchars($hijo['Nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-item">
            <label for="menu">Menú:</label>
            <select id="menu" name="menu">
                <option value="">Todos</option>
                <?php foreach ($menus as $menu) : ?>
                    <option value="<?php echo $menu['Id']; ?>" <?php if ($filtro_menu == $menu['Id']) echo 'selected'; ?>><?php echo htmlspecialchars($menu['Nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-item">
            <button type="submit">Filtrar</button>
        </div>
    </form>

    <div class="table-container">
        <table>
            <tr>
                <th>Hijo</th>
                <th>Menú</th>
                <th>Fecha de Entrega</th>
                <th>Fecha de Pedido</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
            <?php foreach ($pedidos_viandas as $pedido) : ?>
            <tr>
                <td><?php echo htmlspecialchars($pedido['Hijo']); ?></td>
                <td><?php echo htmlspecialchars($pedido['Menú']); ?></td>
                <td><?php echo htmlspecialchars($pedido['Fecha_entrega']); ?></td>
                <td><?php echo htmlspecialchars($pedido['Fecha_pedido']); ?></td>
                <td><?php echo htmlspecialchars($pedido['Estado']); ?></td>
                <td>
                    <?php if ($pedido['Estado'] == 'Procesando') : ?>
                        <form method="post" action="cancelar_pedido.php">
                            <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars($pedido['Id']); ?>">
                            <button type="submit">Cancelar Pedido</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <h2>Historial de Pedidos de Saldo</h2>
    <div class="table-container">
        <table>
            <tr>
                <th>Saldo</th>
                <th>Estado</th>
                <th>Comprobante</th>
                <th>Fecha de Pedido</th>
            </tr>
            <?php foreach ($pedidos_saldo as $pedido) : ?>
            <tr>
                <td><?php echo number_format($pedido['Saldo'], 2); ?> ARS</td>
                <td><?php echo htmlspecialchars($pedido['Estado']); ?></td>
                <td>
                    <?php if ($pedido['Comprobante']) : ?>
                        <a href="../uploads/<?php echo htmlspecialchars($pedido['Comprobante']); ?>" target="_blank">Ver Comprobante</a>
                    <?php else : ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($pedido['Fecha_pedido']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
