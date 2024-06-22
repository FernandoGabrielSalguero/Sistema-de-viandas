<?php
include '../common/header.php';

// Agregar un padre y sus hijos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_parent'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $children = $_POST['children']; // Array of children names
    $course_ids = $_POST['course_id']; // Array of course ids

    // Insert parent
    $stmt = $pdo->prepare("INSERT INTO parents (username, password, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $password, $email, $phone]);
    $parent_id = $pdo->lastInsertId();

    // Insert children
    foreach ($children as $index => $child_name) {
        $course_id = $course_ids[$index];
        $stmt = $pdo->prepare("INSERT INTO children (name, parent_id, course_id) VALUES (?, ?, ?)");
        $stmt->execute([$child_name, $parent_id, $course_id]);
    }
}

// Eliminar un padre y sus hijos
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM children WHERE parent_id = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("DELETE FROM parents WHERE id = ?");
    $stmt->execute([$id]);
}

// Obtener todos los padres y sus hijos
$stmt = $pdo->prepare("
    SELECT parents.id as parent_id, parents.username, parents.email, parents.phone, 
           children.name as child_name, courses.name as course_name, schools.name as school_name
    FROM parents
    LEFT JOIN children ON parents.id = children.parent_id
    LEFT JOIN courses ON children.course_id = courses.id
    LEFT JOIN schools ON courses.school_id = schools.id
");
$stmt->execute();
$parents_children = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los colegios y cursos para los dropdowns
$stmt = $pdo->prepare("SELECT courses.id as course_id, courses.name as course_name, schools.name as school_name FROM courses JOIN schools ON courses.school_id = schools.id");
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <form action="parents.php" method="post">
        <h2>Añadir padres e hijos</h2>
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
        <label for="email">Correo:</label>
        <input type="email" id="email" name="email" required>
        <label for="phone">WhatsApp:</label>
        <input type="text" id="phone" name="phone" required>

        <div id="children-container">
            <h3>Hijo</h3>
            <div class="child-entry">
                <label for="child_name">Nombre Hijo:</label>
                <input type="text" id="child_name" name="children[]" required>
                <label for="course_id">Curso:</label>
                <select id="course_id" name="course_id[]" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['course_name'] . ' - ' . $course['school_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="button" onclick="addChild()">Añadir un hijo más</button>
        <button type="submit" name="add_parent">Agregar usuario</button>
    </form>
    
    <h2>Parents and Children List</h2>
    <table>
        <thead>
            <tr>
                <th>Nombre Padre</th>
                <th>Correo</th>
                <th>Celular</th>
                <th>Nombre Hijo</th>
                <th>Escuela</th>
                <th>Course</th>
                <th>Curso</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($parents_children as $parent_child): ?>
            <tr>
                <td><?php echo htmlspecialchars($parent_child['username']); ?></td>
                <td><?php echo htmlspecialchars($parent_child['email']); ?></td>
                <td><?php echo htmlspecialchars($parent_child['phone']); ?></td>
                <td><?php echo htmlspecialchars($parent_child['child_name']); ?></td>
                <td><?php echo htmlspecialchars($parent_child['school_name']); ?></td>
                <td><?php echo htmlspecialchars($parent_child['course_name']); ?></td>
                <td>
                    <a href="parents.php?delete_id=<?php echo $parent_child['parent_id']; ?>" onclick="return confirm('Are you sure you want to delete this parent and their children?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function addChild() {
    const container = document.getElementById('children-container');
    const newChildEntry = document.createElement('div');
    newChildEntry.classList.add('child-entry');

    const childNameLabel = document.createElement('label');
    childNameLabel.setAttribute('for', 'child_name');
    childNameLabel.textContent = 'Child Name:';
    newChildEntry.appendChild(childNameLabel);

    const childNameInput = document.createElement('input');
    childNameInput.setAttribute('type', 'text');
    childNameInput.setAttribute('id', 'child_name');
    childNameInput.setAttribute('name', 'children[]');
    childNameInput.required = true;
    newChildEntry.appendChild(childNameInput);

    const courseLabel = document.createElement('label');
    courseLabel.setAttribute('for', 'course_id');
    courseLabel.textContent = 'Course:';
    newChildEntry.appendChild(courseLabel);

    const courseSelect = document.createElement('select');
    courseSelect.setAttribute('id', 'course_id');
    courseSelect.setAttribute('name', 'course_id[]');
    courseSelect.required = true;
    
    <?php foreach ($courses as $course): ?>
        const option = document.createElement('option');
        option.setAttribute('value', '<?php echo $course['course_id']; ?>');
        option.textContent = '<?php echo htmlspecialchars($course['course_name'] . ' - ' . $course['school_name']); ?>';
        courseSelect.appendChild(option);
    <?php endforeach; ?>

    newChildEntry.appendChild(courseSelect);
    container.appendChild(newChildEntry);
}
</script>
</body>
</html>
