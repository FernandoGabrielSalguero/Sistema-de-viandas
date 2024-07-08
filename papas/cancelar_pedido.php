<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'papas') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pedido_id = $_POST['pedido_id'];

    // Obtener el precio del pedido y la fecha límite de cancelación
    $stmt = $pdo->prepare("SELECT pc.Menú_Id, m.Precio, m.Fecha_hora_cancelacion 
                           FROM Pedidos_Comida pc 
                           JOIN `Menú` m ON pc.Menú_Id = m.Id 
                           WHERE pc.Id = ?");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pedido) {
        $precio = $pedido['Precio'];
        $menu_id = $pedido['Menú_Id'];
        $fecha_hora_cancelacion = $pedido['Fecha_hora_cancelacion'];

        // Verificar si la fecha límite de cancelación no ha pasado
        $fecha_actual = new DateTime();
        $fecha_limite = new DateTime($fecha_hora_cancelacion);

        if ($fecha_actual <= $fecha_limite) {
            // Cancelar el pedido
            $stmt = $pdo->prepare("UPDATE Pedidos_Comida SET Estado = 'Cancelado' WHERE Id = ?");
            if ($stmt->execute([$pedido_id])) {
                // Devolver el saldo al usuario
                $usuario_id = $_SESSION['usuario_id'];
                $stmt = $pdo->prepare("UPDATE Usuarios SET Saldo = Saldo + ? WHERE Id = ?");
                $stmt->execute([$precio, $usuario_id]);
                header("Location: dashboard.php?success=Pedido cancelado con éxito.");
            } else {
                header("Location: dashboard.php?error=Error al cancelar el pedido.");
            }
        } else {
            $mensaje_error = "La vianda se podia cancelar hasta: " . $fecha_hora_cancelacion;
            header("Location: dashboard.php?error=" . urlencode($mensaje_error));
        }
    } else {
        header("Location: dashboard.php?error=Pedido no encontrado.");
    }
    exit();
}
