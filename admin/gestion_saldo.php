<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Obtener los saldos pendientes de aprobación
$stmt = $pdo->prepare("SELECT ps.Id, ps.Saldo, ps.Usuario_Id, u.Usuario, ps.Estado FROM Pedidos_Saldo ps JOIN Usuarios u ON ps.Usuario_Id = u.Id WHERE ps.Estado = 'Pendiente de aprobación'");
$stmt->execute();
$pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Aprobar saldo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aprobar'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("UPDATE Pedidos_Saldo SET Estado = 'Aprobado' WHERE Id = ?");
    if ($stmt->execute([$id])) {
        // Obtener el saldo y el Usuario_Id del pedido aprobado
        $stmt = $pdo->prepare("SELECT Saldo, Usuario_Id FROM Pedidos_Saldo WHERE Id = ?");
        $stmt->execute([$id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        $saldo = $pedido['Saldo'];
        $usuario_id = $pedido['Usuario_Id'];

        // Actualizar el saldo del usuario
        $stmt = $pdo->prepare("UPDATE Usuarios SET Saldo = Saldo + ? WHERE Id = ?");
        $stmt->execute([$saldo, $usuario_id]);

        $success = "Saldo aprobado con éxito.";
        
        // Obtener el correo del usuario
        $stmt = $pdo->prepare("SELECT Correo FROM Usuarios WHERE Id = ?");
        $stmt->execute([$usuario_id]);
        $correo = $stmt->fetchColumn();

        // Enviar correo al usuario (simplificado para este ejemplo)
        mail($correo, "Saldo aprobado", "Su saldo ha sido aprobado y ya está disponible para la compra de viandas.");
    } else {
        $error = "Hubo un error al aprobar el saldo.";
    }

    // Obtener los saldos pendientes de nuevo después de la aprobación
    $stmt = $pdo->prepare("SELECT ps.Id, ps.Saldo, ps.Usuario_Id, u.Usuario, ps.Estado FROM Pedidos_Saldo ps JOIN Usuarios u ON ps.Usuario_Id = u.Id WHERE ps.Estado = 'Pendiente de aprobación'");
    $stmt->execute();
    $pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Saldos</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Gestión de Saldos</h1>
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
            <th>Acción</th>
        </tr>
        <?php foreach ($pendientes as $pendiente) : ?>
        <tr>
            <td><?php echo htmlspecialchars($pendiente['Id'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pendiente['Usuario'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pendiente['Saldo'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pendiente['Estado'] ?? ''); ?></td>
            <td>
                <form method="post" action="gestion_saldo.php">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($pendiente['Id'] ?? ''); ?>">
                    <button type="submit" name="aprobar">Aprobar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
