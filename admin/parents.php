<?php
include '../common/header.php';

// Agregar un padre
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_parent'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $email = $_POST['email'];

    $stmt = $pdo->prepare("INSERT INTO parents (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute([$username, $password, $email]);
}

// Eliminar un padre
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM parents WHERE id = ?");
    $stmt->execute([$id]);
}

// Obtener todos los padres
$stmt = $pdo->prepare("SELECT * FROM parents");
$stmt->execute();
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <form action="parents.php" method="post">
        <h2>Add Parent</h2>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit" name="add_parent">Add Parent</button>
    </form>
    <h2>Parents List</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($parents as $parent): ?>
            <tr>
                <td><?php echo htmlspecialchars($parent['username']); ?></td>
                <td><?php echo htmlspecialchars($parent['email']); ?></td>
                <td>
                    <a href="parents.php?delete_id=<?php echo $parent['id']; ?>" onclick="return confirm('Are you sure you want to delete this parent?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
