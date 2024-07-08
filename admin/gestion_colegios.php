<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Obtener los colegios y sus cursos
$stmt = $pdo->prepare("SELECT c.Id as ColegioId, c.Nombre as ColegioNombre, c.Dirección, cu.Id as CursoId, cu.Nombre as CursoNombre
                        FROM Colegios c
                        LEFT JOIN Cursos cu ON c.Id = cu.Colegio_Id
                        ORDER BY c.Id, cu.Id");
$stmt->execute();
$colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Actualizar el nombre del colegio o curso
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $colegio_id = $_POST['colegio_id'];
    $colegio_nombre = $_POST['colegio_nombre'];
    $colegio_direccion = $_POST['colegio_direccion'];

    $curso_id = $_POST['curso_id'];
    $curso_nombre = $_POST['curso_nombre'];

    // Actualizar nombre del colegio
    $stmt = $pdo->prepare("UPDATE Colegios SET Nombre = ?, Dirección = ? WHERE Id = ?");
    $stmt->execute([$colegio_nombre, $colegio_direccion, $colegio_id]);

    // Actualizar nombre del curso
    $stmt = $pdo->prepare("UPDATE Cursos SET Nombre = ? WHERE Id = ?");
    $stmt->execute([$curso_nombre, $curso_id]);

    $success = "Colegio y curso actualizados con éxito.";

    // Recargar los datos después de la actualización
    $stmt = $pdo->prepare("SELECT c.Id as ColegioId, c.Nombre as ColegioNombre, c.Dirección, cu.Id as CursoId, cu.Nombre as CursoNombre
                            FROM Colegios c
                            LEFT JOIN Cursos cu ON c.Id = cu.Colegio_Id
                            ORDER BY c.Id, cu.Id");
    $stmt->execute();
    $colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Agregar un nuevo curso a un colegio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_course'])) {
    $colegio_id = $_POST['colegio_id'];
    $curso_nombre = $_POST['nuevo_curso'];

    // Insertar nuevo curso
    $stmt = $pdo->prepare("INSERT INTO Cursos (Nombre, Colegio_Id) VALUES (?, ?)");
    $stmt->execute([$curso_nombre, $colegio_id]);

    $success = "Nuevo curso agregado con éxito.";

    // Recargar los datos después de agregar el curso
    $stmt = $pdo->prepare("SELECT c.Id as ColegioId, c.Nombre as ColegioNombre, c.Dirección, cu.Id as CursoId, cu.Nombre as CursoNombre
                            FROM Colegios c
                            LEFT JOIN Cursos cu ON c.Id = cu.Colegio_Id
                            ORDER BY c.Id, cu.Id");
    $stmt->execute();
    $colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Eliminar un colegio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_colegio'])) {
    $colegio_id = $_POST['colegio_id'];

    // Eliminar los cursos asociados al colegio
    $stmt = $pdo->prepare("DELETE FROM Cursos WHERE Colegio_Id = ?");
    $stmt->execute([$colegio_id]);

    // Eliminar el colegio
    $stmt = $pdo->prepare("DELETE FROM Colegios WHERE Id = ?");
    $stmt->execute([$colegio_id]);

    $success = "Colegio y cursos asociados eliminados con éxito.";

    // Recargar los datos después de la eliminación
    $stmt = $pdo->prepare("SELECT c.Id as ColegioId, c.Nombre as ColegioNombre, c.Dirección, cu.Id as CursoId, cu.Nombre as CursoNombre
                            FROM Colegios c
                            LEFT JOIN Cursos cu ON c.Id = cu.Colegio_Id
                            ORDER BY c.Id, cu.Id");
    $stmt->execute();
    $colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Eliminar un curso
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_curso'])) {
    $curso_id = $_POST['curso_id'];

    // Eliminar el curso
    $stmt = $pdo->prepare("DELETE FROM Cursos WHERE Id = ?");
    $stmt->execute([$curso_id]);

    $success = "Curso eliminado con éxito.";

    // Recargar los datos después de la eliminación
    $stmt = $pdo->prepare("SELECT c.Id as ColegioId, c.Nombre as ColegioNombre, c.Dirección, cu.Id as CursoId, cu.Nombre as CursoNombre
                            FROM Colegios c
                            LEFT JOIN Cursos cu ON c.Id = cu.Colegio_Id
                            ORDER BY c.Id, cu.Id");
    $stmt->execute();
    $colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Colegios</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Gestión de Colegios</h1>
    <?php
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <table>
        <tr>
            <th>Colegio</th>
            <th>Dirección</th>
            <th>Curso</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($colegios as $colegio) : ?>
        <tr>
            <form method="post" action="gestion_colegios.php">
                <td>
                    <input type="hidden" name="colegio_id" value="<?php echo htmlspecialchars($colegio['ColegioId'] ?? ''); ?>">
                    <input type="text" name="colegio_nombre" value="<?php echo htmlspecialchars($colegio['ColegioNombre'] ?? ''); ?>" required>
                </td>
                <td>
                    <input type="text" name="colegio_direccion" value="<?php echo htmlspecialchars($colegio['Dirección'] ?? ''); ?>" required>
                </td>
                <td>
                    <input type="hidden" name="curso_id" value="<?php echo htmlspecialchars($colegio['CursoId'] ?? ''); ?>">
                    <input type="text" name="curso_nombre" value="<?php echo htmlspecialchars($colegio['CursoNombre'] ?? ''); ?>" required>
                </td>
                <td>
                    <button type="submit" name="update">Actualizar</button>
                    <button type="submit" name="delete_colegio" onclick="return confirm('¿Está seguro de que desea eliminar este colegio y sus cursos asociados?');">Eliminar Colegio</button>
                    <button type="submit" name="delete_curso" onclick="return confirm('¿Está seguro de que desea eliminar este curso?');">Eliminar Curso</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
    <h2>Agregar Nuevo Curso</h2>
    <form method="post" action="gestion_colegios.php">
        <label for="colegio_id">Colegio</label>
        <select id="colegio_id" name="colegio_id" required>
            <?php
            $stmt = $pdo->query("SELECT Id, Nombre FROM Colegios");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value=\"{$row['Id']}\">{$row['Nombre']}</option>";
            }
            ?>
        </select>
        <label for="nuevo_curso">Nombre del Nuevo Curso</label>
        <input type="text" id="nuevo_curso" name="nuevo_curso" required>
        <button type="submit" name="add_course">Agregar Curso</button>
    </form>
</body>
</html>
