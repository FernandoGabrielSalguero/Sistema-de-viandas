<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="../../public/css/style.css"> <!-- Ajusta la ruta según sea necesario -->
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>
        <form action="../controllers/userController.php" method="POST">
            <input type="hidden" name="action" value="login">
            <div>
                <label>Usuario:</label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>
