<?php
include '../common/header.php';

// Verificar si el usuario tiene rol de administrador
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener la lista de colegios para asignar al representante
try {
    $stmt = $pdo->query("SELECT id, name FROM schools ORDER BY name");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $schools = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    $school_id = $_POST['school_id'];

    try {
        // Crear el usuario
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $role]);
        $user_id = $pdo->lastInsertId();

        // Asignar el representante a la escuela si el rol es school
        if ($role === 'school') {
            $stmt = $pdo->prepare("UPDATE schools SET rep_id = ? WHERE id = ?");
            $stmt->execute([$user_id, $school_id]);
        }

        $message = "Usuario creado exitosamente.";
    } catch (Exception $e) {
        error_log($e->getMessage());
        $message = "Error al crear el usuario.";
    }
}
?>

<div class="container">
    <h2>Crear Usuario</h2>

    <?php if (isset($message)): ?>
        <div class="toast"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="create_user.php" method="post">
        <label for="username">Nombre de Usuario:</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>

        <label for="role">Rol:</label>
        <select id="role" name="role" required>
            <option value="admin">Administrador</option>
            <option value="parent">Padre</option>
            <option value="kitchen">Cocina</option>
            <option value="school">Representante de Escuela</option>
        </select>

        <label for="school_id">Escuela (solo para representantes):</label>
        <select id="school_id" name="school_id">
            <option value="">Seleccione una escuela</option>
            <?php foreach ($schools as $school): ?>
                <option value="<?php echo $school['id']; ?>"><?php echo htmlspecialchars($school['name']); ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Crear Usuario</button>
    </form>
</div>

<style>
.container {
    padding: 20px;
}

form {
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 5px;
    font-size: 16px;
}

input[type="text"], input[type="email"], input[type="password"], select {
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 16px;
    box-sizing: border-box;
    width: 100%;
}

button {
    padding: 10px;
    background-color: #ff0000;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 16px;
}

button:hover {
    background-color: #a04545;
}

.toast {
    background-color: #333;
    color: white;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
}
</style>

<?php
include '../common/footer.php';
?>
