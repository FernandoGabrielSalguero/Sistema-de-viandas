<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Procesar el formulario cuando se envíe para crear un hijo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_hijo'])) {
    $nombre_hijo = $_POST['nombre_hijo'];
    $colegio_id = $_POST['colegio_id'];
    $curso_id = $_POST['curso_id'];
    $preferencia_id = $_POST['preferencia_id'];

    // Validar que todos los campos estén llenos
    if (empty($nombre_hijo) || empty($colegio_id) || empty($curso_id) || empty($preferencia_id)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Insertar el nuevo hijo en la base de datos
        $stmt = $pdo->prepare("INSERT INTO Hijos (Nombre, Colegio_Id, Curso_Id, Preferencias_Alimenticias) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nombre_hijo, $colegio_id, $curso_id, $preferencia_id])) {
            $success = "Hijo creado con éxito.";
        } else {
            $error = "Hubo un error al crear el hijo.";
        }
    }
}

// Procesar el formulario cuando se envíe para actualizar un hijo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_hijo'])) {
    $hijo_id = $_POST['hijo_id'];
    $nombre_hijo = $_POST['nombre_hijo'];
    $colegio_id = $_POST['colegio_id'];
    $curso_id = $_POST['curso_id'];
    $preferencia_id = $_POST['preferencia_id'];

    // Validar que todos los campos estén llenos
    if (empty($nombre_hijo) || empty($colegio_id) || empty($curso_id) || empty($preferencia_id)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Actualizar el hijo en la base de datos
        $stmt = $pdo->prepare("UPDATE Hijos SET Nombre = ?, Colegio_Id = ?, Curso_Id = ?, Preferencias_Alimenticias = ? WHERE Id = ?");
        if ($stmt->execute([$nombre_hijo, $colegio_id, $curso_id, $preferencia_id, $hijo_id])) {
            $success = "Hijo actualizado con éxito.";
        } else {
            $error = "Hubo un error al actualizar el hijo.";
        }
    }
}

// Procesar la eliminación de un hijo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_hijo'])) {
    $hijo_id = $_POST['hijo_id'];

    // Eliminar el hijo de la base de datos
    $stmt = $pdo->prepare("DELETE FROM Hijos WHERE Id = ?");
    if ($stmt->execute([$hijo_id])) {
        $success = "Hijo eliminado con éxito.";
    } else {
        $error = "Hubo un error al eliminar el hijo.";
    }

    // Recargar la página después de eliminar el hijo
    header("Location: agregar_hijo.php");
    exit();
}

// Obtener todos los hijos
$stmt = $pdo->prepare("SELECT h.Id, h.Nombre, h.Colegio_Id, h.Curso_Id, h.Preferencias_Alimenticias, c.Nombre AS Colegio, cu.Nombre AS Curso
                       FROM Hijos h
                       JOIN Colegios c ON h.Colegio_Id = c.Id
                       JOIN Cursos cu ON h.Curso_Id = cu.Id");
$stmt->execute();
$hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los colegios y cursos para el formulario de creación y actualización de hijos
$stmt = $pdo->prepare("SELECT Id, Nombre FROM Colegios");
$stmt->execute();
$colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT Id, Nombre FROM Cursos");
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todas las preferencias alimenticias
$stmt = $pdo->prepare("SELECT Id, Nombre FROM Preferencias_Alimenticias");
$stmt->execute();
$preferencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar y Administrar Hijos</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Agregar y Administrar Hijos</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <form method="post" action="agregar_hijo.php">
        <h2>Crear Hijo</h2>
        <label for="nombre_hijo">Nombre del Hijo</label>
        <input type="text" id="nombre_hijo" name="nombre_hijo" required>
        
        <label for="colegio_id">Seleccionar Colegio</label>
        <select id="colegio_id" name="colegio_id" required>
            <option value="">Seleccione un colegio</option>
            <?php foreach ($colegios as $colegio) : ?>
                <option value="<?php echo htmlspecialchars($colegio['Id']); ?>"><?php echo htmlspecialchars($colegio['Nombre']); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="curso_id">Seleccionar Curso</label>
        <select id="curso_id" name="curso_id" required>
            <option value="">Seleccione un curso</option>
            <?php foreach ($cursos as $curso) : ?>
                <option value="<?php echo htmlspecialchars($curso['Id']); ?>"><?php echo htmlspecialchars($curso['Nombre']); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="preferencia_id">Preferencias Alimenticias</label>
        <select id="preferencia_id" name="preferencia_id" required>
            <option value="">Seleccione una preferencia</option>
            <?php foreach ($preferencias as $preferencia) : ?>
                <option value="<?php echo htmlspecialchars($preferencia['Id']); ?>"><?php echo htmlspecialchars($preferencia['Nombre']); ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="crear_hijo">Crear Hijo</button>
    </form>

    <h2>Lista de Hijos</h2>
    <table>
        <tr>
            <th>Nombre del Hijo</th>
            <th>Colegio</th>
            <th>Curso</th>
            <th>Preferencias Alimenticias</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($hijos as $hijo) : ?>
            <tr>
                <form method="post" action="agregar_hijo.php">
                    <td><input type="text" name="nombre_hijo" value="<?php echo htmlspecialchars($hijo['Nombre']); ?>" required></td>
                    <td>
                        <select name="colegio_id" required>
                            <?php foreach ($colegios as $colegio) : ?>
                                <option value="<?php echo htmlspecialchars($colegio['Id']); ?>" <?php echo ($colegio['Id'] == $hijo['Colegio_Id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($colegio['Nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="curso_id" required>
                            <?php foreach ($cursos as $curso) : ?>
                                <option value="<?php echo htmlspecialchars($curso['Id']); ?>" <?php echo ($curso['Id'] == $hijo['Curso_Id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($curso['Nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="preferencia_id" required>
                            <?php foreach ($preferencias as $preferencia) : ?>
                                <option value="<?php echo htmlspecialchars($preferencia['Id']); ?>" <?php echo ($preferencia['Id'] == $hijo['Preferencias_Alimenticias']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($preferencia['Nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="hijo_id" value="<?php echo htmlspecialchars($hijo['Id']); ?>">
                        <button type="submit" name="actualizar_hijo">Actualizar</button>
                        <button type="submit" name="eliminar_hijo" onclick="return confirm('¿Está seguro de que desea eliminar este hijo?');">Eliminar</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
