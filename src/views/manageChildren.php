<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Hijos</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Gestión de Hijos</h2>
        <form action="../controllers/childrenController.php" method="POST">
            <input type="hidden" name="action" value="add">
            <div>
                <label>Nombre:</label>
                <input type="text" name="name" required>
            </div>
            <div>
                <label>Colegio:</label>
                <select name="school" required>
                    <!-- Opciones de colegios cargadas dinámicamente -->
                </select>
            </div>
            <div>
                <label>Curso:</label>
                <select name="course" required>
                    <!-- Opciones de cursos cargadas dinámicamente -->
                </select>
            </div>
            <div>
                <label>Preferencias Alimenticias:</label>
                <input type="text" name="preferences">
            </div>
            <button type="submit">Agregar Hijo</button>
        </form>

        <!-- Aquí iría el código para listar y la opción de editar o eliminar hijos -->
    </div>
</body>
</html>
