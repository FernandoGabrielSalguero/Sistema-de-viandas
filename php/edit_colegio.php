<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];

    $sql = "UPDATE colegios SET nombre='$nombre' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../views/manage_colegios.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM colegios WHERE id=$id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $colegio = $result->fetch_assoc();
        } else {
            echo "Colegio no encontrado.";
            exit();
        }
    } else {
        echo "ID del colegio no especificado.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Colegio</title>
</head>
<body>
    <h3>Editar Colegio</h3>
    <form action="edit_colegio.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $colegio['id']; ?>">
        <div class="input-group">
            <label for="nombre">Nombre del Colegio:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo $colegio['nombre']; ?>" required>
        </div>
        <button type="submit">Guardar Cambios</button>
    </form>
</body>
</html>
