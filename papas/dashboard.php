<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$query_pedidos = "SELECT pc.Id, h.Nombre as Hijo, m.Nombre as Menú, m.Fecha_entrega, pc.Fecha_pedido, pc.Estado
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
$stmt = $pdo->prepare("SELECT Id, Saldo, Estado, Comprobante, Fecha_pedido FROM Pedidos_Saldo WHERE Usuario_Id = ?");
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
</head>
<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($usuario['Nombre']); ?></h1>
    <p>Correo: <?php echo htmlspecialchars($usuario['Correo']); ?></p>
    <p>Saldo disponible: <?php echo number_format($usuario['Saldo'], 2); ?> ARS</p>

    <?php
    if (isset($_GET['error'])) {
        echo "<p class='error'>" . htmlspecialchars($_GET['error']) . "</p>";
    }
    if (isset($_GET['success'])) {
        echo "<p class='success'>" . htmlspecialchars($_GET['success']) . "</p>";
    }
    ?>

    <h2>Historial de Pedidos de Viandas</h2>
    <form method="get" action="dashboard.php">
        <label for="fecha_entrega">Fecha de Entrega:</label>
        <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($filtro_fecha_entrega); ?>">
        
        <label for="estado">Estado:</label>
        <select id="estado" name="estado">
            <option value="">Todos</option>
            <option value="Procesando" <?php if ($filtro_estado == 'Procesando') echo 'selected'; ?>>Procesando</option>
            <option value="Cancelado" <?php if ($filtro_estado == 'Cancelado') echo 'selected'; ?>>Cancelado</option>
        </select>
        
        <label for="hijo">Hijo:</label>
        <select id="hijo" name="hijo">
            <option value="">Todos</option>
            <?php foreach ($hijos as $hijo) : ?>
                <option value="<?php echo $hijo['Id']; ?>" <?php if ($filtro_hijo == $hijo['Id']) echo 'selected'; ?>><?php echo htmlspecialchars($hijo['Nombre']); ?></option>
            <?php endforeach; ?>
        </select>
        
        <label for="menu">Menú:</label>
        <select id="menu" name="menu">
            <option value="">Todos</option>
            <?php foreach ($menus as $menu) : ?>
                <option value="<?php echo $menu['Id']; ?>" <?php if ($filtro_menu == $menu['Id']) echo 'selected'; ?>><?php echo htmlspecialchars($menu['Nombre']); ?></option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit">Filtrar</button>
    </form>

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

    <h2>Historial de Pedidos de Saldo</h2>
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
</body>
</html>
