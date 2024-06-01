<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

$error_message = '';
$success_message = '';

// Handle form submission to add or edit users
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $id = $_POST['id'];

    // Validate input
    if (empty($name) || empty($surname) || empty($username) || empty($password) || empty($phone) || empty($email) || empty($role)) {
        $error_message = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'El correo electrónico no es válido.';
    } else {
        if ($id) {
            // Update existing user
            $query = "UPDATE users SET name = ?, surname = ?, username = ?, password = ?, phone = ?, email = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssi", $name, $surname, $username, $password, $phone, $email, $role, $id);
        } else {
            // Insert new user
            $query = "INSERT INTO users (name, surname, username, password, phone, email, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssss", $name, $surname, $username, $password, $phone, $email, $role);
        }

        if ($stmt->execute()) {
            if (!$id) {
                $user_id = $stmt->insert_id;
                // Add children for the new user
                $children = json_decode($_POST['children'], true);
                foreach ($children as $child) {
                    $child_name = $child['name'];
                    $child_surname = $child['surname'];
                    $child_class = $child['class'];
                    $child_school = $child['school'];
                    $child_grades = $child['grades'];

                    $query = "INSERT INTO children (user_id, name, surname, class, school, grades) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("isssss", $user_id, $child_name, $child_surname, $child_class, $child_school, $child_grades);
                    $stmt->execute();
                }
            }
            $success_message = 'Usuario guardado exitosamente.';
        } else {
            $error_message = 'Error al guardar el usuario. Inténtelo de nuevo.';
        }
    }
}

// Fetch users from the database
$query = "SELECT * FROM users";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script>
        let children = [];
        
        function addChild() {
            const childName = document.getElementById('child_name').value;
            const childSurname = document.getElementById('child_surname').value;
            const childClass = document.getElementById('child_class').value;
            const childSchool = document.getElementById('child_school').value;
            const childGrades = document.getElementById('child_grades').value;

            const child = {name: childName, surname: childSurname, class: childClass, school: childSchool, grades: childGrades};
            children.push(child);
            document.getElementById('children').value = JSON.stringify(children);
            
            document.getElementById('child_list').innerHTML += `<li>${childName} ${childSurname} (${childClass}, ${childSchool})</li>`;
            
            // Clear inputs
            document.getElementById('child_name').value = '';
            document.getElementById('child_surname').value = '';
            document.getElementById('child_class').value = '';
            document.getElementById('child_school').value = '';
            document.getElementById('child_grades').value = '';
        }
    </script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_users.php">Gestionar Usuarios</a>
        <a href="manage_menu.php">Gestionar Menú</a>
        <a href="manage_orders.php">Gestionar Pedidos</a>
        <a href="../logout.php">Cerrar Sesión</a>
    </div>
    <div class="main-content">
        <h2>Gestionar Usuarios</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= $error_message ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?= $success_message ?></div>
        <?php endif; ?>

        <form action="manage_users.php" method="POST">
            <input type="hidden" name="id" value="">
            <label for="name">Nombre:</label>
            <input type="text" id="name" name="name" required>
            <label for="surname">Apellido:</label>
            <input type="text" id="surname" name="surname" required>
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            <label for="phone">Teléfono:</label>
            <input type="text" id="phone" name="phone" required>
            <label for="email">Correo:</label>
            <input type="email" id="email" name="email" required>
            <label for="role">Rol:</label>
            <select id="role" name="role" required>
                <option value="admin">Administrador</option>
                <option value="user">Usuario</option>
            </select>
            
            <h3>Hijos</h3>
            <ul id="child_list"></ul>
            <label for="child_name">Nombre del Hijo:</label>
            <input type="text" id="child_name">
            <label for="child_surname">Apellido del Hijo:</label>
            <input type="text" id="child_surname">
            <label for="child_class">Curso:</label>
            <input type="text" id="child_class">
            <label for="child_school">Escuela:</label>
            <input type="text" id="child_school">
            <label for="child_grades">Notas:</label>
            <input type="text" id="child_grades">
            <button type="button" onclick="addChild()">Añadir Hijo</button>
            <input type="hidden" id="children" name="children" value='[]'>
            
            <button type="submit">Guardar</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Usuario</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['surname'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td><?= $row['phone'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['role'] ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $row['id'] ?>">Editar</a>
                            <a href="delete_user.php?id=<?= $row['id'] ?>">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
