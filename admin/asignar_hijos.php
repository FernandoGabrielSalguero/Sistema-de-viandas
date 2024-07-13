<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Procesar el formulario cuando se envíe para asignar un hijo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['asignar_hijo'])) {
    $usuario_id = $_POST['usuario_id'];
    $hijo_id = $_POST['hijo_id'];

    // Validar que todos los campos estén llenos
    if (empty($usuario_id) || empty($hijo_id)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Verificar si la relación ya existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Usuarios_Hijos WHERE Usuario_Id = ? AND Hijo_Id = ?");
        $stmt->execute([$usuario_id, $hijo_id]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $error = "El hijo ya está asignado a este usuario.";
        } else {
            // Insertar la relación entre usuario y hijo en la base de datos
            $stmt = $pdo->prepare("INSERT INTO Usuarios_Hijos (Usuario_Id, Hijo_Id) VALUES (?, ?)");
            if ($stmt->execute([$usuario_id, $hijo_id])) {
                $success = "Hijo asignado con éxito.";
            } else {
                $error = "Hubo un error al asignar el hijo.";
            }
        }
    }
}

// Procesar la eliminación de una asignación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_asignacion'])) {
    $usuario_id = $_POST['usuario_id'];
    $hijo_id = $_POST['hijo_id'];

    // Eliminar la relación entre usuario y hijo en la base de datos
    $stmt = $pdo->prepare("DELETE FROM Usuarios_Hijos WHERE Usuario_Id = ? AND Hijo_Id = ?");
    if ($stmt->execute([$usuario_id, $hijo_id])) {
        $success = "Asignación eliminada con éxito.";
    } else {
        $error = "Hubo un error al eliminar la asignación.";
    }

    // Recargar la página después de eliminar la asignación
    header("Location: asignar_hijos.php");
    exit();
}

// Obtener todos los usuarios con rol "Papás"
$stmt = $pdo->prepare("SELECT Id, Nombre FROM Usuarios WHERE Rol = 'papas'");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los hijos
$stmt = $pdo->prepare("SELECT h.Id, h.Nombre, h.Colegio_Id, h.Curso_Id, h.Preferencias_Alimenticias, c.Nombre AS Colegio, cu.Nombre AS Curso
                       FROM Hijos h
                       JOIN Colegios c ON h.Colegio_Id = c.Id
                       JOIN Cursos cu ON h.Curso_Id = cu.Id");
$stmt->execute();
$hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar y depurar cada paso de la consulta

// Paso 5: Añadir Join con Preferencias_Alimenticias
$query = "
    SELECT uh.Usuario_Id, uh.Hijo_Id, u.Nombre AS NombrePapa, h.Nombre AS NombreHijo, 
           c.Nombre AS Colegio, cu.Nombre AS Curso, p.Nombre AS Preferencia
    FROM Usuarios_Hijos uh
    JOIN Usuarios u ON uh.Usuario_Id = u.Id
    JOIN Hijos h ON uh.Hijo_Id = h.Id
    JOIN Colegios c ON h.Colegio_Id = c.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    JOIN Preferencias_Alimenticias p ON h.Preferencias_Alimenticias = p.Id
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
echo "Paso 5 - Query: $query\n";
echo "Asignaciones:\n";
var_dump($asignaciones);
echo "</pre>";
exit();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Hijos a Papás</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Asignar Hijos a Papás</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <form method="post" action="asignar_hijos.php">
        <h2>Asignar Hijo a Papá</h2>
        <label for="usuario_id">Seleccionar Papá</label>
        <select id="usuario_id" name="usuario_id" required>
            <option value="">Seleccione un papá</option>
            <?php foreach ($usuarios as $usuario) : ?>
                <option value="<?php echo htmlspecialchars($usuario['Id']); ?>"><?php echo htmlspecialchars($usuario['Nombre']); ?></option>
            <?php endforeach; ?>
        </select>
        
        <label for="hijo_id">Seleccionar Hijo</label>
        <select id="hijo_id" name="hijo_id" required>
            <option value="">Seleccione un hijo</option>
            <?php foreach ($hijos as $hijo) : ?>
                <option value="<?php echo htmlspecialchars($hijo['Id']); ?>">
                    <?php echo htmlspecialchars($hijo['Nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" name="asignar_hijo">Asignar Hijo</button>
    </form>

    <h2>Lista de Hijos Asignados</h2>
    <table>
        <tr>
            <th>Nombre del Papá</th>
            <th>Nombre del Hijo</th>
            <th>Colegio</th>
            <th>Curso</th>
            <th>Preferencias Alimenticias</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($asignaciones as $asignacion) : ?>
            <tr>
                <form method="post" action="asignar_hijos.php">
                    <td><?php echo htmlspecialchars($asignacion['NombrePapa']); ?></td>
                    <td><?php echo htmlspecialchars($asignacion['NombreHijo']); ?></td>
                    <td><?php echo htmlspecialchars($asignacion['Colegio']); ?></td>
                    <td><?php echo htmlspecialchars($asignacion['Curso']); ?></td>
                    <td><?php echo htmlspecialchars($asignacion['Preferencia']); ?></td>
                    <td>
                        <input type="hidden" name="hijo_id" value="<?php echo htmlspecialchars($asignacion['Hijo_Id']); ?>">
                        <input type="hidden" name="usuario_id" value="<?php echo htmlspecialchars($asignacion['Usuario_Id']); ?>">
                        <button type="submit" name="eliminar_asignacion" onclick="return confirm('¿Está seguro de que desea eliminar esta asignación?');">Eliminar</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
