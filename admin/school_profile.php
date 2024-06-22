<?php
include '../common/header.php';

// Verificar si el usuario tiene rol de administrador
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$school_id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    // Crear un nuevo colegio
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    try {
        $stmt = $pdo->prepare("INSERT INTO schools (name, address, phone) VALUES (?, ?, ?)");
        $stmt->execute([$name, $address, $phone]);
        $message = "Nuevo colegio creado exitosamente.";
    } catch (Exception $e) {
        error_log($e->getMessage());
        $message = "Error al crear el colegio.";
    }
}

if ($school_id) {
    // Obtener la información de la escuela
    try {
        $stmt = $pdo->prepare("SELECT * FROM schools WHERE id = ?");
        $stmt->execute([$school_id]);
        $school = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$school) {
            header("Location: schools.php");
            exit;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        header("Location: schools.php");
        exit;
    }

    // Obtener los cursos de la escuela
    try {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE school_id = ? ORDER BY name");
        $stmt->execute([$school_id]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        $courses = [];
    }

    // Obtener los estudiantes de la escuela
    try {
        $stmt = $pdo->prepare("
            SELECT children.*, parents.name as parent_name, courses.name as course_name
            FROM children
            JOIN parents ON children.parent_id = parents.id
            JOIN courses ON children.course_id = courses.id
            WHERE courses.school_id = ?
            ORDER BY courses.name, children.name
        ");
        $stmt->execute([$school_id]);
        $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        $children = [];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $name = $_POST['name'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];

        try {
            $stmt = $pdo->prepare("UPDATE schools SET name = ?, address = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $address, $phone, $school_id]);
            $message = "La información de la escuela ha sido actualizada.";
            $school['name'] = $name;
            $school['address'] = $address;
            $school['phone'] = $phone;
        } catch (Exception $e) {
            error_log($e->getMessage());
            $message = "Error al actualizar la información de la escuela.";
        }
    }
}
?>

<div class="container">
    <h2>Perfil de la Escuela: <?php echo htmlspecialchars($school['name'] ?? 'Nueva Escuela'); ?></h2>

    <?php if (isset($message)): ?>
        <div class="toast"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="school_profile.php<?php echo $school_id ? '?id=' . $school_id : ''; ?>" method="post">
        <h3>Información de la Escuela</h3>
        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($school['name'] ?? ''); ?>" required>

        <label for="address">Dirección:</label>
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($school['address'] ?? ''); ?>" required>

        <label for="phone">Teléfono:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($school['phone'] ?? ''); ?>" required>

        <?php if ($school_id): ?>
            <button type="submit" name="update">Actualizar Información</button>
        <?php else: ?>
            <button type="submit" name="create">Crear Colegio</button>
        <?php endif; ?>
    </form>

    <?php if ($school_id): ?>
        <h3>Cursos</h3>
        <table>
            <thead>
                <tr>
                    <th>Nombre del Curso</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?php echo htmlspecialchars($course['name']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Estudiantes</h3>
        <table>
            <thead>
                <tr>
                    <th>Nombre del Estudiante</th>
                    <th>Curso</th>
                    <th>Padre</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($children as $child): ?>
                <tr>
                    <td><?php echo htmlspecialchars($child['name']); ?></td>
                    <td><?php echo htmlspecialchars($child['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($child['parent_name']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Eliminar Colegio</h3>
        <form action="school_profile.php" method="post">
            <input type="hidden" name="school_id" value="<?php echo $school_id; ?>">
            <button type="submit" name="delete" onclick="return confirm('¿Estás seguro de que deseas eliminar este colegio?')">Eliminar Colegio</button>
        </form>
    <?php endif; ?>
</div>

<style>
.container {
    padding: 20px;
}

.toast {
    background-color: #333;
    color: white;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
}

form {
    margin-bottom: 40px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-size: 16px;
}

input[type="text"], input[type="password"], input[type="email"], input[type="number"], select {
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

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table thead {
    background-color: #af4c4c;
    color: white;
}

table th, table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #f1f1f1;
}

table th {
    font-weight: bold;
}
</style>

<?php
include '../common/footer.php';
?>
