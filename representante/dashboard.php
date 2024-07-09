<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: ../index.php");
    exit();
}

include '../includes/header_admin.php';
include '../includes/db.php';

// Obtener la lista de representantes y sus asignaciones
$stmt = $pdo->prepare("SELECT rc.Id, u.Nombre as Representante, c.Nombre as Colegio
                       FROM Representantes_Colegios rc
                       JOIN Usuarios u ON rc.Representante_Id = u.Id
                       JOIN Colegios c ON rc.Colegio_Id = c.Id");
$stmt->execute();
$representantes_colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener listas para los selectores
$stmt = $pdo->prepare("SELECT Id, Nombre FROM Usuarios WHERE Rol = 'representante'");
$stmt->execute();
$representantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT Id, Nombre FROM Colegios");
$stmt->execute();
$colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar la asignación de un representante a un colegio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['asignar'])) {
    $representante_id = $_POST['representante_id'];
    $colegio_id = $_POST['colegio_id'];

    $stmt = $pdo->prepare("INSERT INTO Representantes_Colegios (Representante_Id, Colegio_Id) VALUES (?, ?)");
    if ($stmt->execute([$representante_id, $colegio_id])) {
        header("Location: gestion_representantes.php?success=Representante asignado con éxito.");
    } else {
        header("Location: gestion_representantes.php?error=Error al asignar representante.");
    }
    exit();
}

// Manejar la eliminación de una asignación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar'])) {
    $asignacion_id = $_POST['asignacion_id'];

    $stmt = $pdo->prepare("DELETE FROM Representantes_Colegios WHERE Id = ?");
    if ($stmt->execute([$asignacion_id])) {
        header("Location: gestion_representantes.php?success=Asignación eliminada con éxito.");
    } else {
        header("Location: gestion_representantes.php?error=Error al eliminar asignación.");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Representantes</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Gestión de Representantes</h1>

    <?php
    if (isset($_GET['error'])) {
        echo "<p class='error'>" . htmlspecialchars($_GET['error']) . "</p>";
    }
    if (isset($_GET['success'])) {
        echo "<p class='success'>" . htmlspecialchars($_GET['success']) . "</p>";
    }
    ?>

    <h2>Asignar Representante a Colegio</h2>
    <form method="post" action="gestion_representantes.php">
        <label for="representante_id">Representante:</label>
        <select id="representante_id" name="representante_id" required>
            <option value="">Seleccione un representante</option>
            <?php foreach ($representantes as $representante) : ?>
                <option value="<?php echo htmlspecialchars($representante['Id']); ?>"><?php echo htmlspecialchars($representante['Nombre']); ?></option>
            <?php endforeach; ?>
        </select>
        
        <label for="colegio_id">Colegio:</label>
        <select id="colegio_id" name="colegio_id" required>
            <option value="">Seleccione un colegio</option>
            <?php foreach ($colegios as $colegio) : ?>
                <option value="<?php echo htmlspecialchars($colegio['Id']); ?>"><?php echo htmlspecialchars($colegio['Nombre']); ?></option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" name="asignar">Asignar</button>
    </form>

    <h2>Asignaciones Actuales</h2>
    <table>
        <tr>
            <th>Representante</th>
            <th>Colegio</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($representantes_colegios as $asignacion) : ?>
        <tr>
            <td><?php echo htmlspecialchars($asignacion['Representante']); ?></td>
            <td><?php echo htmlspecialchars($asignacion['Colegio']); ?></td>
            <td>
                <form method="post" action="gestion_representantes.php" onsubmit="return confirm('¿Está seguro de que desea eliminar esta asignación?');">
                    <input type="hidden" name="asignacion_id" value="<?php echo htmlspecialchars($asignacion['Id']); ?>">
                    <button type="submit" name="eliminar">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
