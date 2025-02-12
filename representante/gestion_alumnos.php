<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'representante') {
    header("Location: ../index.php");
    exit();
}

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_representante.php';
include '../includes/db.php';

$usuario_id = $_SESSION['usuario_id'];

// Obtener el colegio asignado al representante
$stmt = $pdo->prepare("SELECT Colegio_Id FROM Representantes_Colegios WHERE Representante_Id = ?");
$stmt->execute([$usuario_id]);
$colegio_id = $stmt->fetchColumn();

// Obtener todos los cursos del colegio
$stmt = $pdo->prepare("SELECT Id, Nombre FROM Cursos WHERE Colegio_Id = ?");
$stmt->execute([$colegio_id]);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener alumnos del colegio
$stmt = $pdo->prepare("
    SELECT h.Id AS Hijo_Id, h.Nombre AS Nombre_Alumno, cu.Id AS Curso_Id, cu.Nombre AS Nombre_Curso 
    FROM Hijos h
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    WHERE h.Colegio_Id = ?
");
$stmt->execute([$colegio_id]);
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar el cambio de curso
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cambiar_curso'])) {
    $hijo_id = $_POST['hijo_id'];
    $nuevo_curso = $_POST['curso_id'];

    $stmt = $pdo->prepare("UPDATE Hijos SET Curso_Id = ? WHERE Id = ?");
    if ($stmt->execute([$nuevo_curso, $hijo_id])) {
        $success = "Curso actualizado con éxito.";
        header("Location: gestion_alumnos.php"); // Recargar la página
        exit();
    } else {
        $error = "Hubo un error al actualizar el curso.";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Alumnos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style_representante.css">
</head>
<body>
    <h1>Gestión de Alumnos</h1>

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
            <th>ID Alumno</th>
            <th>Nombre del Alumno</th>
            <th>Curso Actual</th>
            <th>Cambiar Curso</th>
        </tr>
        <?php foreach ($alumnos as $alumno) : ?>
        <tr>
            <td><?php echo htmlspecialchars($alumno['Hijo_Id']); ?></td>
            <td><?php echo htmlspecialchars($alumno['Nombre_Alumno']); ?></td>
            <td><?php echo htmlspecialchars($alumno['Nombre_Curso']); ?></td>
            <td>
                <form method="post" action="gestion_alumnos.php">
                    <input type="hidden" name="hijo_id" value="<?php echo $alumno['Hijo_Id']; ?>">
                    <select name="curso_id">
                        <?php foreach ($cursos as $curso) : ?>
                            <option value="<?php echo $curso['Id']; ?>" <?php echo ($curso['Id'] == $alumno['Curso_Id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($curso['Nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="cambiar_curso">Actualizar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
