<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Viandas School</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/app.js" defer></script>
</head>
<body>
    <div class="login-container">
        <h1>Iniciar Sesión</h1>
        <form id="loginForm">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>
