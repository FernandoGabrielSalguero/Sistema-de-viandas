<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';
include '../includes/load_env.php';

// Cargar variables del archivo .env
loadEnv(__DIR__ . '/../.env');

// Función para enviar correo electrónico usando SMTP
function enviarCorreo($to, $subject, $message) {
    $headers = "From: " . getenv('SMTP_USERNAME') . "\r\n" .
               "Reply-To: " . getenv('SMTP_USERNAME') . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Configuración del transporte SMTP
    $params = [
        'host' => getenv('SMTP_HOST'),
        'port' => getenv('SMTP_PORT'),
        'auth' => true,
        'username' => getenv('SMTP_USERNAME'),
        'password' => getenv('SMTP_PASSWORD'),
    ];

    // Usar la función mail() de PHP
    ini_set('SMTP', $params['host']);
    ini_set('smtp_port', $params['port']);
    ini_set('sendmail_from', $params['username']);

    return mail($to, $subject, $message, $headers);
}

// Parámetros de paginación
$itemsPerPage = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Obtener la cantidad total de registros
$totalItemsQuery = $pdo->query("SELECT COUNT(*) FROM Pedidos_Saldo");
$totalItems = $totalItemsQuery->fetchColumn();

// Calcular el número total de páginas
$totalPages = ceil($totalItems / $itemsPerPage);

// Obtener los registros de la página actual, incluyendo el nombre del usuario
$stmt = $pdo->prepare("SELECT ps.Id, ps.Usuario_Id, u.Nombre AS Usuario_Nombre, ps.Saldo, ps.Estado, ps.Comprobante, ps.Fecha_pedido 
                       FROM Pedidos_Saldo ps
                       JOIN Usuarios u ON ps.Usuario_Id = u.Id
                       ORDER BY ps.Id DESC
                       LIMIT :offset, :itemsPerPage");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();
$pedidosSaldo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cambiar el estado del saldo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cambiar_estado'])) {
    $id = $_POST['id'];
    $nuevo_estado = $_POST['estado'];

    // Obtener el pedido de saldo específico
    $stmt = $pdo->prepare("SELECT Usuario_Id, Saldo, Estado FROM Pedidos_Saldo WHERE Id = ?");
    $stmt->execute([$id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pedido) {
        $usuario_id = $pedido['Usuario_Id'];
        $saldo = $pedido['Saldo'];

        // Verificar si el estado es "Aprobado" o "Cancelado"
        if ($nuevo_estado == 'Aprobado' && $pedido['Estado'] != 'Aprobado') {
            // Sumar el saldo al saldo del usuario si es aprobado
            $stmt = $pdo->prepare("UPDATE Usuarios SET Saldo = Saldo + ? WHERE Id = ?");
            $stmt->execute([$saldo, $usuario_id]);

            // Enviar correo de confirmación
            $stmt = $pdo->prepare("SELECT Correo FROM Usuarios WHERE Id = ?");
            $stmt->execute([$usuario_id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            $correo = $usuario['Correo'];

            $subject = "Saldo Aprobado";
            $message = "Estimado usuario,\n\nSu saldo de $saldo ARS ha sido aprobado y acreditado en su cuenta.\n\nSaludos,\nEquipo de Ilmana Gastronomía";

            if (!enviarCorreo($correo, $subject, $message)) {
                $error = "No se pudo enviar el correo electrónico al usuario.";
            }
        } elseif ($nuevo_estado == 'Cancelado') {
            // Si el nuevo estado es "Cancelado", no hacer nada con el saldo, solo actualizar el estado
        }

        // Actualizar el estado del pedido de saldo en la base de datos
        $stmt = $pdo->prepare("UPDATE Pedidos_Saldo SET Estado = ? WHERE Id = ?");
        if ($stmt->execute([$nuevo_estado, $id])) {
            $success = "Estado del saldo actualizado con éxito.";
        } else {
            $error = "Hubo un error al actualizar el estado del saldo: " . implode(", ", $stmt->errorInfo());
        }
    }
}



?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Saldo</title>
    <link rel="stylesheet" href="../css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>Gestión de Saldo</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Saldo</th>
            <th>Estado</th>
            <th>Comprobante</th>
            <th>Fecha y Hora</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($pedidosSaldo as $pedido) : ?>
        <tr>
            <td><?php echo htmlspecialchars($pedido['Id']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Usuario_Nombre']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Saldo']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Estado'] ?? 'Desconocido'); ?></td>
            <td><a href="../uploads/<?php echo htmlspecialchars($pedido['Comprobante']); ?>" target="_blank">Ver Comprobante</a></td>
            <td><?php echo htmlspecialchars($pedido['Fecha_pedido']); ?></td>
            <td>
                <form method="post" action="gestion_saldo.php">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($pedido['Id']); ?>">
                    <select name="estado">
                        <option value="Pendiente de aprobación" <?php echo ($pedido['Estado'] == 'Pendiente de aprobación') ? 'selected' : ''; ?>>Pendiente de aprobación</option>
                        <option value="Aprobado" <?php echo ($pedido['Estado'] == 'Aprobado') ? 'selected' : ''; ?>>Aprobado</option>
                        <option value="Rechazado" <?php echo ($pedido['Estado'] == 'Rechazado') ? 'selected' : ''; ?>>Rechazado</option>
                    </select>
                    <button type="submit" name="cambiar_estado">Cambiar Estado</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">&laquo; Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <strong><?php echo $i; ?></strong>
            <?php else: ?>
                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Siguiente &raquo;</a>
        <?php endif; ?>
    </div>
</body>
</html>
