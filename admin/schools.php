<?php
include '../common/header.php';

// Agregar un colegio
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_school'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];

    $stmt = $pdo->prepare("INSERT INTO schools (name, address) VALUES (?, ?)");
    $stmt->execute([$name, $address]);
}

// Eliminar un colegio
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM schools WHERE id = ?");
    $stmt->execute([$id]);
}

// Obtener todos los colegios
$stmt = $pdo->prepare("SELECT * FROM schools");
$stmt->execute();
$schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <form action="schools.php" method="post">
        <h2>Add School</h2>
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required>
        <button type="submit" name="add_school">Add School</button>
    </form>
    <h2>Schools List</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schools as $school): ?>
            <tr>
                <td><?php echo htmlspecialchars($school['name']); ?></td>
                <td><?php echo htmlspecialchars($school['address']); ?></td>
                <td>
                    <a href="schools.php?delete_id=<?php echo $school['id']; ?>" onclick="return confirm('Are you sure you want to delete this school?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
