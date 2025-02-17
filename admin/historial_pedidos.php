<?php
ob_start(); // Inicia el almacenamiento en búfer de salida

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/header_admin.php'; // Encabezado para administradores
include '../includes/db.php'; // Conexión a la base de datos

// Verificar que el usuario es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Eliminar pedido si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_pedido'])) {
    $pedido_id = $_POST['pedido_id'];

    $stmt = $pdo->prepare("DELETE FROM Pedidos_Cuyo_Placa WHERE id = ?");
    if ($stmt->execute([$pedido_id])) {
        $success = "Pedido eliminado con éxito.";
    } else {
        $error = "Hubo un error al eliminar el pedido.";
    }
}

$stmt = $pdo->query("SELECT * FROM Pedidos_Cuyo_Placa ORDER BY fecha DESC");
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_end_flush(); // Finaliza el almacenamiento en búfer y envía la salida al navegador
?>

// Obtener todos los pedidos
$stmt = $pdo->query("SELECT * FROM Pedidos_Cuyo_Placa ORDER BY fecha DESC");
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Historial de Pedidos</title>
    <link rel="stylesheet" href="../css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <h1>Historial de Pedidos</h1>

    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario ID</th>
                <th>Fecha</th>
                <th>Creado en</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($pedido['id']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['usuario_id']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['fecha']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['created_at']); ?></td>
                    <td>
                        <form method="post" action="historial_pedidos.php" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este pedido?');">
                            <input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars($pedido['id']); ?>">
                            <button type="submit" name="eliminar_pedido">Eliminar Pedido</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>

</html>
