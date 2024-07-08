<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuarios</title>
    <link rel="stylesheet" href="../../public/css/style.css"> <!-- Asegúrate de ajustar la ruta al CSS según necesites -->
</head>
<body>
    <div class="container">
        <h2>Registro de Usuario</h2>
        <form action="../controllers/userController.php" method="POST">
            <input type="hidden" name="action" value="register">
            <div>
                <label>Nombre:</label>
                <input type="text" name="name" required>
            </div>
            <div>
                <label>Usuario:</label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>
            <div>
                <label>Teléfono:</label>
                <input type="text" name="phone">
            </div>
            <div>
                <label>Correo Electrónico:</label>
                <input type="email" name="email" required>
            </div>
            <div>
                <label>Rol:</label>
                <select name="role" required>
                    <option value="papas">Papás</option>
                    <option value="cocina">Cocina</option>
                    <option value="representante">Representante</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>
            <button type="submit">Registrar Usuario</button>
        </form>
    </div>
</body>
</html>
