<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Procesar el formulario cuando se envíe para crear una nueva preferencia alimenticia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_preferencia'])) {
    $nombre_preferencia = $_POST['nombre_preferencia'];

    // Validar que el campo no esté vacío
    if (empty($nombre_preferencia)) {
        $error = "El campo de preferencia alimenticia es obligatorio.";
    } else {
        // Insertar la nueva preferencia en la base de datos
        $stmt = $pdo->prepare("INSERT INTO Preferencias_Alimenticias (Nombre) VALUES (?)");
        if ($stmt->execute([$nombre_preferencia])) {
            $success = "Preferencia alimenticia creada con éxito.";
        } else {
            $error = "Hubo un error al crear la preferencia alimenticia.";
        }
    }
}

// Procesar el formulario para actualizar una preferencia alimenticia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_preferencia'])) {
    $preferencia_id = $_POST['preferencia_id'];
    $nombre_preferencia = $_POST['nombre_preferencia'];

    // Validar que el campo no esté vacío
    if (empty($nombre_preferencia)) {
        $error = "El campo de preferencia alimenticia es obligatorio.";
    } else {
        // Actualizar la preferencia en la base de datos
        $stmt = $pdo->prepare("UPDATE Preferencias_Alimenticias SET Nombre = ? WHERE Id = ?");
        if ($stmt->execute([$nombre_preferencia, $preferencia_id])) {
            $success = "Preferencia alimenticia actualizada con éxito.";
        } else {
            $error = "Hubo un error al actualizar la preferencia alimenticia.";
        }
    }
}

// Procesar la eliminación de una preferencia alimenticia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_preferencia'])) {
    $preferencia_id = $_POST['preferencia_id'];

    // Eliminar la preferencia de la base de datos
    $stmt = $pdo->prepare("DELETE FROM Preferencias_Alimenticias WHERE Id = ?");
    if ($stmt->execute([$preferencia_id])) {
        $success = "Preferencia alimenticia eliminada con éxito.";
    } else {
        $error = "Hubo un error al eliminar la preferencia alimenticia.";
    }
}

// Obtener todas las preferencias alimenticias
$stmt = $pdo->prepare("SELECT * FROM Preferencias_Alimenticias");
$stmt->execute();
$preferencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Preferencias Alimenticias</title>
    <link rel="stylesheet" href="../css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>Alta de Preferencias Alimenticias</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <form method="post" action="alta_preferencias.php">
        <label for="nombre_preferencia">Nombre de la Preferencia Alimenticia</label>
        <input type="text" id="nombre_preferencia" name="nombre_preferencia" required>
        <button type="submit" name="crear_preferencia">Crear Preferencia</button>
    </form>

    <h2>Lista de Preferencias Alimenticias</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($preferencias as $preferencia) : ?>
        <tr>
            <form method="post" action="alta_preferencias.php">
                <td><?php echo htmlspecialchars($preferencia['Id']); ?></td>
                <td><input type="text" name="nombre_preferencia" value="<?php echo htmlspecialchars($preferencia['Nombre']); ?>" required></td>
                <td>
                    <input type="hidden" name="preferencia_id" value="<?php echo htmlspecialchars($preferencia['Id']); ?>">
                    <button type="submit" name="actualizar_preferencia">Actualizar</button>
                    <button type="submit" name="eliminar_preferencia" onclick="return confirm('¿Está seguro de que desea eliminar esta preferencia alimenticia?');">Eliminar</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
