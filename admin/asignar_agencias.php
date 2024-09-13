<?php

session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: ../login.php");
    exit();
}

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';
include 'header_admin.php';

// Obtener todos los usuarios hyt_admin
$stmt_admin = $pdo->prepare("SELECT Id, Nombre FROM Usuarios WHERE Rol = 'hyt_admin'");
$stmt_admin->execute();
$hyt_admins = $stmt_admin->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los usuarios hyt_agencia
$stmt_agencia = $pdo->prepare("SELECT Id, Nombre FROM Usuarios WHERE Rol = 'hyt_agencia'");
$stmt_agencia->execute();
$hyt_agencias = $stmt_agencia->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario de asignación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['asignar_agencia'])) {
    $admin_id = $_POST['hyt_admin_id'];
    $agencia_id = $_POST['hyt_agencia_id'];

    // Insertar la relación en la base de datos
    $stmt = $pdo->prepare("INSERT INTO hyt_admin_agencia (hyt_admin_id, hyt_agencia_id) VALUES (?, ?)");
    if ($stmt->execute([$admin_id, $agencia_id])) {
        $success = "Agencia asignada correctamente.";
    } else {
        $error = "Error al asignar la agencia.";
    }
}

// Obtener asignaciones actuales para mostrar
$query = "SELECT a.Nombre as admin_nombre, g.Nombre as agencia_nombre
          FROM hyt_admin_agencia aa
          JOIN Usuarios a ON aa.hyt_admin_id = a.Id
          JOIN Usuarios g ON aa.hyt_agencia_id = g.Id";
$stmt_asignaciones = $pdo->prepare($query);
$stmt_asignaciones->execute();
$asignaciones = $stmt_asignaciones->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Agencias a HYT Admin</title>
    <link rel="stylesheet" href="../css/hyt_variables.css">
</head>
<body>
    <h1>Asignar Agencias a HYT Admin</h1>
    <?php
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    ?>

    <form method="POST" action="asignar_agencias.php">
        <label for="hyt_admin_id">Seleccionar HYT Admin:</label>
        <select id="hyt_admin_id" name="hyt_admin_id" required>
            <?php foreach ($hyt_admins as $admin): ?>
                <option value="<?php echo $admin['Id']; ?>"><?php echo $admin['Nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="hyt_agencia_id">Seleccionar Agencia:</label>
        <select id="hyt_agencia_id" name="hyt_agencia_id" required>
            <?php foreach ($hyt_agencias as $agencia): ?>
                <option value="<?php echo $agencia['Id']; ?>"><?php echo $agencia['Nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="asignar_agencia">Asignar Agencia</button>
    </form>

    <h2>Asignaciones actuales</h2>
    <table>
        <thead>
            <tr>
                <th>HYT Admin</th>
                <th>Agencia</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($asignaciones as $asignacion): ?>
            <tr>
                <td><?php echo htmlspecialchars($asignacion['admin_nombre']); ?></td>
                <td><?php echo htmlspecialchars($asignacion['agencia_nombre']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
