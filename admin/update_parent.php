<?php
include '../common/header.php';

$parent_id = $_GET['parent_id'];
$child_id = $_GET['child_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_parent'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $child_name = $_POST['child_name'];
    $course_id = $_POST['course_id'];

    // Actualizar padre
    $stmt = $pdo->prepare("UPDATE parents SET username = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->execute([$username, $email, $phone, $parent_id]);

    // Actualizar hijo
    $stmt = $pdo->prepare("UPDATE children SET name = ?, course_id = ? WHERE id = ?");
    $stmt->execute([$child_name, $course_id, $child_id]);

    header("Location: parents.php");
    exit();
}

// Obtener los datos actuales del padre y su hijo
$stmt = $pdo->prepare("
    SELECT parents.username, parents.email, parents.phone, 
           children.name as child_name, children.course_id
    FROM parents
    JOIN children ON parents.id = children.parent_id
    WHERE parents.id = ? AND children.id = ?
");
$stmt->execute([$parent_id, $child_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener todos los cursos para el dropdown
$stmt = $pdo->prepare("SELECT courses.id as course_id, courses.name as course_name, schools.name as school_name FROM courses JOIN schools ON courses.school_id = schools.id");
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <form action="update_parent.php?parent_id=<?php echo $parent_id; ?>&child_id=<?php echo $child_id; ?>" method="post">
        <h2>Actualizar Información del Padre y Hijo</h2>
        <label for="username">Nombre de Usuario:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($data['username']); ?>" required>
        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>" required>
        <label for="phone">Teléfono:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($data['phone']); ?>" required>

        <h3>Hijo</h3>
        <label for="child_name">Nombre del Hijo:</label>
        <input type="text" id="child_name" name="child_name" value="<?php echo htmlspecialchars($data['child_name']); ?>" required>
        <label for="course_id">Curso:</label>
        <select id="course_id" name="course_id" required>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo $course['course_id']; ?>" <?php echo $course['course_id'] == $data['course_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($course['course_name'] . ' - ' . $course['school_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="update_parent">Actualizar Información</button>
    </form>
</div>
</body>
</html>
