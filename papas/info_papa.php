<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'papas') {
    echo "Acceso no autorizado";
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener información adicional del usuario o cualquier otro contenido que desees mostrar
$stmt = $pdo->prepare("SELECT Nombre, Correo, Saldo FROM Usuarios WHERE Id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<div class="info-papa">
    <h2>Información Adicional del Papá</h2>
    <p>Nombre: <?php echo htmlspecialchars($usuario['Nombre']); ?></p>
    <p>Correo: <?php echo htmlspecialchars($usuario['Correo']); ?></p>
    <p>Saldo disponible: <?php echo number_format($usuario['Saldo'], 2); ?> ARS</p>
    <!-- Agrega aquí más contenido o funcionalidades específicas -->
</div>
