<?php
// edit_curso.php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];

    $sql = "UPDATE cursos SET nombre='$nombre' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../views/manage_cursos.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM cursos WHERE id=$id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $curso = $result->fetch_assoc();
        } else {
            echo "Curso no encontrado.";
            exit();
        }
    } else {
        echo "ID del curso no especificado.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso</title>
</head>
<body>
    <h3>Editar Curso</h3>
    <form action="edit_curso.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $curso['id']; ?>">
        <div class="input-group">
            <label for="nombre">Nombre del Curso:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo $curso['nombre']; ?>" required>
        </div>
        <button type="submit">Guardar Cambios</button>
    </form>
</body>
</html>
