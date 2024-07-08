<?php
session_start();
include '../includes/header_papas.php';
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'papas') {
    header("Location: ../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT h.Id, h.Nombre, c.Nombre as Colegio, cu.Nombre as Curso FROM Hijos h JOIN Colegios c ON h.Colegio_Id = c.Id JOIN Cursos cu ON h.Curso_Id = cu.Id JOIN Usuarios_Hijos uh ON h.Id = uh.Hijo_Id WHERE uh.Usuario_Id = ?");
$stmt->execute([$usuario_id]);
$hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['hijo_id'] = $_POST['hijo_id'];
    header("Location: comprar_viandas.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seleccionar Hijo</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Seleccionar Hijo</h1>
    <form method="post" action="seleccionar_hijo.php">
        <label for="hijo_id">Seleccione un hijo:</label>
        <select id="hijo_id" name="hijo_id" required>
            <option value="">Seleccione un hijo</option>
            <?php foreach ($hijos as $hijo) : ?>
                <option value="<?php echo htmlspecialchars($hijo['Id']); ?>"><?php echo htmlspecialchars($hijo['Nombre'] . " - " . $hijo['Colegio'] . " - " . $hijo['Curso']); ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <button type="submit">Seleccionar Hijo</button>
    </form>
</body>
</html>
