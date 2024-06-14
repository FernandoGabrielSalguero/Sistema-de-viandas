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

    // Insertar padre
    $stmt = $pdo->prepare("INSERT INTO parents (username, password, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $password, $email, $phone]);
    $parent_id = $pdo->lastInsertId();

    // Insertar hijos
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
           children.id as child_id, children.name as child_name, courses.name as course_name, schools.name as school_name
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
        <h2>Agregar Padre y Hijos</h2>
        <label for="username">Nombre de Usuario:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>
        <label for="phone">Teléfono:</label>
        <input type="text" id="phone" name="phone" required>

        <div id="children-container">
            <h3>Hijos</h3>
            <div class="child-entry">
                <label for="child_name">Nombre del Hijo:</label>
                <input type="text" id="child_name" name="children[]" required>
                <label for="course_id">Curso:</label>
                <select id="course_id" name="course_id[]" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['course_id']; ?>"><?php echo htmlspecialchars($course['course_name'] . ' - ' . $course['school_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <button type="button" onclick="addChild()">Agregar Otro Hijo</button>
        <button type="submit" name="add_parent">Agregar Padre y Hijos</button>
    </form>
    
    <h2>Lista de Padres e Hijos</h2>
    <table id="parentsTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">Nombre del Padre</th>
                <th onclick="sortTable(1)">Correo Electrónico</th>
                <th onclick="sortTable(2)">Teléfono</th>
                <th onclick="sortTable(3)">Nombre del Hijo</th>
                <th onclick="sortTable(4)">Colegio</th>
                <th onclick="sortTable(5)">Curso</th>
                <th>Acciones</th>
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
                    <a href="parents.php?delete_id=<?php echo $parent_child['parent_id']; ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar a este padre y sus hijos?')">Eliminar</a>
                    <a href="update_parent.php?parent_id=<?php echo $parent_child['parent_id']; ?>&child_id=<?php echo $parent_child['child_id']; ?>">Actualizar</a>
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
    childNameLabel.textContent = 'Nombre del Hijo:';
    newChildEntry.appendChild(childNameLabel);

    const childNameInput = document.createElement('input');
    childNameInput.setAttribute('type', 'text');
    childNameInput.setAttribute('id', 'child_name');
    childNameInput.setAttribute('name', 'children[]');
    childNameInput.required = true;
    newChildEntry.appendChild(childNameInput);

    const courseLabel = document.createElement('label');
    courseLabel.setAttribute('for', 'course_id');
    courseLabel.textContent = 'Curso:';
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

function sortTable(columnIndex) {
    const table = document.getElementById('parentsTable');
    let rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    switching = true;
    dir = "asc"; 
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[columnIndex];
            y = rows[i].getElementsByTagName("TD")[columnIndex + 1];
            if (dir === "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir === "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++; 
        } else {
            if (switchcount === 0 && dir === "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}
</script>
</body>
</html>
