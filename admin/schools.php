<?php
include '../common/header.php';

// Verificar si el usuario tiene rol de administrador
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener la lista de colegios
try {
    $stmt = $pdo->query("SELECT schools.*, users.username as rep_name FROM schools LEFT JOIN users ON schools.rep_id = users.id ORDER BY schools.name");
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $schools = [];
}

// Obtener la lista de posibles representantes
try {
    $stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'school' ORDER BY username");
    $reps = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $reps = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_id = $_POST['school_id'];
    $rep_id = $_POST['rep_id'];

    try {
        $stmt = $pdo->prepare("UPDATE schools SET rep_id = ? WHERE id = ?");
        $stmt->execute([$rep_id, $school_id]);
        header("Location: schools.php");
        exit;
    } catch (Exception $e) {
        error_log($e->getMessage());
        $error = "Error al asignar el representante.";
    }
}
?>

<div class="container">
    <h2>Gestionar Colegios</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>Representante</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schools as $school): ?>
            <tr>
                <td><?php echo htmlspecialchars($school['name']); ?></td>
                <td><?php echo htmlspecialchars($school['address']); ?></td>
                <td><?php echo htmlspecialchars($school['phone']); ?></td>
                <td><?php echo htmlspecialchars($school['rep_name'] ?? 'Sin asignar'); ?></td>
                <td>
                    <form action="schools.php" method="post">
                        <select name="rep_id">
                            <option value="">Sin asignar</option>
                            <?php foreach ($reps as $rep): ?>
                            <option value="<?php echo $rep['id']; ?>" <?php echo ($school['rep_id'] == $rep['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rep['username']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="school_id" value="<?php echo $school['id']; ?>">
                        <button type="submit">Asignar</button>
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

select {
    padding: 5px;
    margin-right: 10px;
}

button {
    padding: 5px 10px;
    background-color: #af4c4c;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}

button:hover {
    background-color: #8c3b3b;
}

.error {
    color: red;
    margin-bottom: 20px;
}
</style>

<?php
include '../common/footer.php';
?>
