<?php
include '../common/header.php';

// Agregar un hijo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_child'])) {
    $name = $_POST['name'];
    $parent_id = $_POST['parent_id'];
    $course_id = $_POST['course_id'];

    $stmt = $pdo->prepare("INSERT INTO children (name, parent_id, course_id) VALUES (?, ?, ?)");
    $stmt->execute([$name, $parent_id, $course_id]);
}

// Eliminar un hijo
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM children WHERE id = ?");
    $stmt->execute([$id]);
}

// Obtener todos los hijos
$stmt = $pdo->prepare("SELECT children.*, parents.username as parent_name, courses.name as course_name FROM children JOIN parents ON children.parent_id = parents.id JOIN courses ON children.course_id = courses.id");
$stmt->execute();
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los padres para el dropdown
$stmt = $pdo->prepare("SELECT * FROM parents");
$stmt->execute();
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los cursos para el dropdown
$stmt = $pdo->prepare("SELECT * FROM courses");
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <form action="children.php" method="post">
        <h2>Add Child</h2>
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <label for="parent_id">Parent:</label>
        <select id="parent_id" name="parent_id" required>
            <?php foreach ($parents as $parent): ?>
                <option value="<?php echo $parent['id']; ?>"><?php echo htmlspecialchars($parent['username']); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="course_id">Course:</label>
        <select id="course_id" name="course_id" required>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="add_child">Add Child</button>
    </form>
    <h2>Children List</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Parent</th>
                <th>Course</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($children as $child): ?>
            <tr>
                <td><?php echo htmlspecialchars($child['name']); ?></td>
                <td><?php echo htmlspecialchars($child['parent_name']); ?></td>
                <td><?php echo htmlspecialchars($child['course_name']); ?></td>
                <td>
                    <a href="children.php?delete_id=<?php echo $child['id']; ?>" onclick="return confirm('Are you sure you want to delete this child?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
