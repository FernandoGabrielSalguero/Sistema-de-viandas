<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Procesar el formulario cuando se envíe
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_colegio = $_POST['nombre_colegio'];
    $direccion = $_POST['direccion'];
    $cursos = $_POST['cursos']; // Esto es un array de nombres de cursos

    // Validar que todos los campos estén llenos
    if (empty($nombre_colegio) || empty($direccion) || empty($cursos)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Insertar el nuevo colegio en la base de datos
        $stmt = $pdo->prepare("INSERT INTO Colegios (Nombre, Dirección) VALUES (?, ?)");
        if ($stmt->execute([$nombre_colegio, $direccion])) {
            $colegio_id = $pdo->lastInsertId();

            // Insertar los cursos asociados al colegio
            $stmt = $pdo->prepare("INSERT INTO Cursos (Nombre, Colegio_Id) VALUES (?, ?)");
            foreach ($cursos as $curso) {
                $stmt->execute([$curso, $colegio_id]);
            }

            $success = "Colegio y cursos creados con éxito.";
        } else {
            $error = "Hubo un error al crear el colegio.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Colegios</title>
    <link rel="stylesheet" href="../css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        function addCurso() {
            const cursosContainer = document.getElementById('cursosContainer');
            const newInput = document.createElement('input');
            newInput.type = 'text';
            newInput.name = 'cursos[]';
            newInput.placeholder = 'Nombre del Curso';
            cursosContainer.appendChild(newInput);
        }
    </script>
</head>
<body>
    <h1>Alta de Colegios</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <form method="post" action="alta_colegios.php">
        <label for="nombre_colegio">Nombre del Colegio</label>
        <input type="text" id="nombre_colegio" name="nombre_colegio" required>
        
        <label for="direccion">Dirección</label>
        <input type="text" id="direccion" name="direccion" required>
        
        <label for="cursos">Cursos</label>
        <div id="cursosContainer">
            <input type="text" name="cursos[]" placeholder="Nombre del Curso" required>
        </div>
        <button type="button" onclick="addCurso()">Agregar Curso</button>
        
        <button type="submit">Crear Colegio</button>
    </form>
</body>
</html>
