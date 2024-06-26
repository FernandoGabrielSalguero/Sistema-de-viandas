<?php
include '../Common/db_connect.php';
session_start();

if ($_SESSION['role'] != 'Administrador') {
    header('Location: ../login.php');
    exit();
}

$query_users = "SELECT * FROM usuarios";
$result_users = $conn->query($query_users);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    <div class="crud-container">
        <h1>Gestión de Usuarios</h1>
        <form id="user-form" method="post" action="user_process.php">
            <input type="hidden" name="id" id="user-id">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" required>
            <label for="celular">Celular:</label>
            <input type="text" id="celular" name="celular" required>
            <label for="correo">Correo Electrónico:</label>
            <input type="email" id="correo" name="correo" required>
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            <label for="rol">Rol:</label>
            <select id="rol" name="rol" required>
                <option value="Administrador">Administrador</option>
                <option value="Usuario">Usuario</option>
                <option value="Cocina">Cocina</option>
                <option value="School Leader">School Leader</option>
            </select>
            <div id="extra-fields">
                <label for="hijos">Hijos:</label>
                <div id="hijos-container"></div>
                <button type="button" onclick="addHijo()">Agregar Hijo</button>
            </div>
            <button type="submit">Guardar</button>
        </form>

        <h2>Usuarios Registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Celular</th>
                    <th>Correo Electrónico</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result_users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['nombre']; ?></td>
                    <td><?php echo $user['apellido']; ?></td>
                    <td><?php echo $user['celular']; ?></td>
                    <td><?php echo $user['correo']; ?></td>
                    <td><?php echo $user['usuario']; ?></td>
                    <td><?php echo $user['rol']; ?></td>
                    <td>
                        <button onclick="editUser(<?php echo $user['id']; ?>)">Editar</button>
                        <button onclick="deleteUser(<?php echo $user['id']; ?>)">Eliminar</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="../notifications_service.js"></script>
    <script>
        function addHijo() {
            const container = document.getElementById('hijos-container');
            const index = container.children.length;
            const hijoFields = `
                <div class="hijo-fields">
                    <label for="hijo_nombre_${index}">Nombre del Hijo:</label>
                    <input type="text" name="hijo_nombre[]" id="hijo_nombre_${index}" required>
                    <label for="colegio_${index}">Colegio:</label>
                    <select name="colegio[]" id="colegio_${index}" required></select>
                    <label for="curso_${index}">Curso:</label>
                    <select name="curso[]" id="curso_${index}" required></select>
                    <label for="preferencias_${index}">Preferencias Alimenticias:</label>
                    <input type="text" name="preferencias[]" id="preferencias_${index}">
                </div>
            `;
            container.insertAdjacentHTML('beforeend', hijoFields);
            // Cargar colegios y cursos según la lógica de tu sistema
        }

        function editUser(id) {
            // Implementar lógica de edición
        }

        function deleteUser(id) {
            // Implementar lógica de eliminación
        }
    </script>
</body>
</html>
