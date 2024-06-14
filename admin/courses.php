<?php
include '../common/header.php';

// Agregar un curso
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $name = $_POST['name'];
    $school_id = $_POST['school_id'];

    $stmt = $pdo->prepare("INSERT INTO courses (name, school_id) VALUES (?, ?)");
    $stmt->execute([$name, $school_id]);
}

// Eliminar un curso
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
}

// Obtener todos los cursos
$stmt = $pdo->prepare("SELECT courses.*, schools.name as school_name FROM courses JOIN schools ON courses.school_id = schools.id");
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los colegios para el dropdown
$stmt = $pdo->prepare("SELECT * FROM schools");
$stmt->execute();
$schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <form action="courses.php" method="post">
        <h2>Añadir curso</h2>
        <label for="name">Nombre del curso:</label>
        <input type="text" id="name" name="name" required>
        <label for="school_id">School:</label>
        <select id="school_id" name="school_id" required>
            <?php foreach ($schools as $school): ?>
                <option value="<?php echo $school['id']; ?>"><?php echo htmlspecialchars($school['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="add_course">Añadir cursos</button>
    </form>
    <h2>Listado de cursos</h2>
    <table>
        <thead>
            <tr>
                <th>Curso</th>
                <th>Escuela</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
            <tr>
                <td><?php echo htmlspecialchars($course['name']); ?></td>
                <td><?php echo htmlspecialchars($course['school_name']); ?></td>
                <td>
                    <a href="courses.php?delete_id=<?php echo $course['id']; ?>" onclick="return confirm('Are you sure you want to delete this course?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
