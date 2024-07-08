<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Gestión de Usuarios</h2>
        <form action="../controllers/userController.php" method="POST">
            <input type="hidden" name="action" value="addUser">
            <!-- Campos para agregar nuevo usuario -->
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
                <label>Rol:</label>
                <select name="role" required>
                    <option value="administrador">Administrador</option>
                    <option value="papas">Papás</option>
                    <option value="cocina">Cocina</option>
                    <option value="representante">Representante</option>
                </select>
            </div>
            <button type="submit">Agregar Usuario</button>
        </form>
        <!-- Aquí podrías incluir una tabla para listar y editar/eliminar usuarios -->
    </div>
</body>
</html>
