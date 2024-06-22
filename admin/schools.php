<?php
include '../common/header.php';

// Verificar si el usuario tiene rol de administrador
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener la lista de colegios
try {
    $stmt = $pdo->query("SELECT * FROM schools ORDER BY name");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $schools = [];
}
?>

<div class="container">
    <h2>Gestionar Colegios</h2>
    <a href="school_profile.php" class="button">Crear Nuevo Colegio</a>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schools as $school): ?>
            <tr>
                <td><?php echo htmlspecialchars($school['name']); ?></td>
                <td><?php echo htmlspecialchars($school['address']); ?></td>
                <td><?php echo htmlspecialchars($school['phone']); ?></td>
                <td>
                    <a href="school_profile.php?id=<?php echo $school['id']; ?>" class="button">Editar</a>
                    <form action="schools.php" method="post" style="display:inline-block;">
                        <input type="hidden" name="school_id" value="<?php echo $school['id']; ?>">
                        <button type="submit" name="delete" class="button" onclick="return confirm('¿Estás seguro de que deseas eliminar este colegio?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.container {
    padding: 20px;
}

.button {
    display: inline-block;
    padding: 10px 20px;
    margin-bottom: 10px;
    background-color: #ff0000;
    color: white;
    text-decoration: none;
    border-radius: 3px;
}

.button:hover {
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $school_id = $_POST['school_id'];

    try {
        // Primero eliminar todas las relaciones dependientes
        $stmt = $pdo->prepare("DELETE FROM courses WHERE school_id = ?");
        $stmt->execute([$school_id]);

        $stmt = $pdo->prepare("DELETE FROM schools WHERE id = ?");
        $stmt->execute([$school_id]);
        header("Location: schools.php");
        exit;
    } catch (Exception $e) {
        error_log($e->getMessage());
        $message = "Error al eliminar el colegio.";
    }
}
?>

<?php include '../common/footer.php'; ?>
