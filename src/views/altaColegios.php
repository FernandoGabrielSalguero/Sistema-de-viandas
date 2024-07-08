<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Colegios</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Gestión de Colegios</h2>
        <form action="../controllers/schoolController.php" method="POST">
            <input type="hidden" name="action" value="addSchool">
            <div>
                <label>Nombre del Colegio:</label>
                <input type="text" name="schoolName" required>
            </div>
            <div>
                <label>Dirección del Colegio:</label>
                <input type="text" name="schoolAddress" required>
            </div>
            <button type="submit">Añadir Colegio</button>
        </form>
    </div>
</body>
</html>
