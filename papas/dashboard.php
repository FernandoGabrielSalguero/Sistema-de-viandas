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

// Obtener información del hijo y su colegio, curso, etc.
$stmt = $pdo->prepare("
    SELECT h.Id, h.Nombre, h.Colegio_Id, h.Curso_Id, h.Preferencias
    FROM Hijos h
    JOIN Usuarios_Hijos uh ON h.Id = uh.Hijo_Id
    WHERE uh.Usuario_Id = ?");
$stmt->execute([$usuario_id]);
$informacion_hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inicializar variables de filtros
$filtro_fecha_entrega = isset($_GET['fecha_entrega']) ? $_GET['fecha_entrega'] : '';
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_hijo = isset($_GET['hijo']) ? $_GET['hijo'] : '';
$filtro_menu = isset($_GET['menu']) ? $_GET['menu'] : '';

// Construir la consulta con filtros y ordenar por ID de pedido de mayor a menor
$query_pedidos = "
    SELECT pc.Id, h.Nombre as Hijo, m.Nombre as Menú, 
           DATE_FORMAT(m.Fecha_entrega, '%d/%b/%y') as Fecha_entrega, 
           DATE_FORMAT(pc.Fecha_pedido, '%d/%b/%y %H:%i:%s') as Fecha_pedido, 
           pc.Estado
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
    JOIN Usuarios_Hijos uh ON h.Id = uh.Hijo_Id
    WHERE uh.Usuario_Id = :usuario_id
    ORDER BY pc.Id DESC";

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

// Paginación
$items_per_page = 10; // Número de pedidos por página
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$query_pedidos .= " LIMIT :offset, :items_per_page";
$params['offset'] = $offset;
$params['items_per_page'] = $items_per_page;

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

$stmt = $pdo->prepare("SELECT Id, Nombre FROM Menú WHERE Estado = 'En venta'");
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Papás</title>
    <link rel="stylesheet" href="../css/style_dashboard_papas.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>Que gusto verte de nuevo, <?php echo htmlspecialchars($usuario['Nombre']); ?></h1>
    <p>Correo: <?php echo htmlspecialchars($usuario['Correo']); ?></p>

    <h2>Información de Hijos</h2>
    <div class="table-container">
        <table class="mat-elevation-z8">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Colegio</th>
                    <th>Curso</th>
                    <th>Preferencias Alimenticias</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($informacion_hijos as $hijo) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($hijo['Id']); ?></td>
                    <td><?php echo htmlspecialchars($hijo['Nombre']); ?></td>
                    <td><?php echo htmlspecialchars($hijo['Colegio']); ?></td>
                    <td><?php echo htmlspecialchars($hijo['Curso']); ?></td>
                    <td><?php echo htmlspecialchars($hijo['Preferencias']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

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
        <table class="mat-elevation-z8">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Hijo</th>
                    <th>Menú</th>
                    <th>Fecha de Entrega</th>
                    <th>Fecha de Pedido</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos_viandas as $pedido) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($pedido['Id']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['Hijo']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['Menú']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($pedido['Fecha_entrega'])); ?></td>
                    <td><?php echo htmlspecialchars($pedido['Fecha_pedido']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['Estado']); ?></td>
                    <td>
                        <?php if ($pedido['Estado'] == 'Procesando' && strtotime($pedido['Fecha_entrega']) > strtotime('today 09:00')) : ?>
                            <form method="post" action="cancelar_pedido.php">
                                <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars($pedido['Id']); ?>">
                                <button type="submit">Cancelar Pedido</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Paginación
    $total_items = $pdo->query("SELECT COUNT(*) FROM Pedidos_Comida WHERE Usuario_Id = $usuario_id")->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);

    echo '<div class="pagination">';
    for ($page = 1; $page <= $total_pages; $page++) {
        echo '<a href="dashboard.php?page=' . $page . '">' . $page . '</a> ';
    }
    echo '</div>';
    ?>

    <h2>Historial de Pedidos de Saldo</h2>
    <div class="table-container">
        <table class="mat-elevation-z8">
            <thead>
                <tr>
                    <th>Saldo</th>
                    <th>Estado</th>
                    <th>Comprobante</th>
                    <th>Fecha de Pedido</th>
                </tr>
            </thead>
            <tbody>
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
            </tbody>
        </table>
    </div>
</body>
</html>
