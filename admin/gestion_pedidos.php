<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Obtener los pedidos
$sql = "SELECT pc.Id, m.Nombre as Nombre_menú, c.Nombre AS ColegioNombre, cu.Nombre AS CursoNombre, 
               h.Nombre AS Nombre_alumno, pc.Fecha_entrega, pc.Estado, 
               COALESCE(pc.Preferencias_alimenticias, 'Sin preferencias alimenticias') AS Preferencias_alimenticias 
        FROM Pedidos_Comida pc 
        JOIN Colegios c ON h.Colegio_Id = c.Id 
        JOIN Cursos cu ON h.Curso_Id = cu.Id 
        JOIN Hijos h ON pc.Hijo_Id = h.Id 
        JOIN Menú m ON pc.Menú_Id = m.Id";
$whereClauses = [];
$params = [];

if (!empty($_GET['colegio'])) {
    $whereClauses[] = "c.Id = ?";
    $params[] = $_GET['colegio'];
}

if (!empty($_GET['curso'])) {
    $whereClauses[] = "cu.Id = ?";
    $params[] = $_GET['curso'];
}

if (!empty($_GET['alumno'])) {
    $whereClauses[] = "h.Nombre LIKE ?";
    $params[] = "%" . $_GET['alumno'] . "%";
}

if (!empty($_GET['fecha'])) {
    $whereClauses[] = "pc.Fecha_entrega = ?";
    $params[] = $_GET['fecha'];
}

if ($whereClauses) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cambiar el estado del pedido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cambiar_estado'])) {
    $id = $_POST['id'];
    $nuevo_estado = $_POST['estado'];
    $stmt = $pdo->prepare("UPDATE Pedidos_Comida SET Estado = ? WHERE Id = ?");
    if ($stmt->execute([$nuevo_estado, $id])) {
        $success = "Estado del pedido actualizado con éxito.";
    } else {
        $error = "Hubo un error al actualizar el estado del pedido.";
    }

    // Obtener los pedidos de nuevo después de la actualización
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pedidos</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Gestión de Pedidos</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <form method="get" action="gestion_pedidos.php">
        <label for="colegio">Colegio</label>
        <select id="colegio" name="colegio">
            <option value="">Todos</option>
            <?php
            $stmt = $pdo->query("SELECT Id, Nombre FROM Colegios");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value=\"{$row['Id']}\">{$row['Nombre']}</option>";
            }
            ?>
        </select>
        
        <label for="curso">Curso</label>
        <select id="curso" name="curso">
            <option value="">Todos</option>
            <?php
            $stmt = $pdo->query("SELECT Id, Nombre FROM Cursos");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value=\"{$row['Id']}\">{$row['Nombre']}</option>";
            }
            ?>
        </select>
        
        <label for="alumno">Nombre del Alumno</label>
        <input type="text" id="alumno" name="alumno">
        
        <label for="fecha">Fecha de Entrega</label>
        <input type="date" id="fecha" name="fecha">
        
        <button type="submit">Filtrar</button>
    </form>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre del Menú</th>
            <th>Colegio</th>
            <th>Curso</th>
            <th>Nombre del Alumno</th>
            <th>Fecha de Entrega</th>
            <th>Estado del Pedido</th>
            <th>Preferencias Alimenticias</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($pedidos as $pedido) : ?>
        <tr>
            <td><?php echo htmlspecialchars($pedido['Id'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pedido['Nombre_menú'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pedido['ColegioNombre'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pedido['CursoNombre'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pedido['Nombre_alumno'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pedido['Fecha_entrega'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pedido['Estado'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pedido['Preferencias_alimenticias'] ?? ''); ?></td>
            <td>
                <form method="post" action="gestion_pedidos.php">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($pedido['Id'] ?? ''); ?>">
                    <select name="estado">
                        <option value="Procesando" <?php echo ($pedido['Estado'] == 'Procesando') ? 'selected' : ''; ?>>Procesando</option>
                        <option value="Cancelado" <?php echo ($pedido['Estado'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        <option value="Entregado" <?php echo ($pedido['Estado'] == 'Entregado') ? 'selected' : ''; ?>>Entregado</option>
                    </select>
                    <button type="submit" name="cambiar_estado">Cambiar Estado</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
