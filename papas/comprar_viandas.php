<?php
session_start();
include '../includes/header_papas.php';
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'papas') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_SESSION['hijo_id'])) {
    header("Location: seleccionar_hijo.php");
    exit();
}

$hijo_id = $_SESSION['hijo_id'];
$usuario_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT m.Id, m.Nombre, m.Fecha_entrega, m.Precio FROM Menu m WHERE m.Estado = 'En venta'");
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $menu_id = $_POST['menu_id'];
    $stmt = $pdo->prepare("SELECT Precio FROM Menu WHERE Id = ?");
    $stmt->execute([$menu_id]);
    $precio = $stmt->fetch(PDO::FETCH_ASSOC)['Precio'];

    // Verificar si el usuario tiene saldo suficiente
    $stmt = $pdo->prepare("SELECT Saldo FROM Usuarios WHERE Id = ?");
    $stmt->execute([$usuario_id]);
    $saldo = $stmt->fetch(PDO::FETCH_ASSOC)['Saldo'];

    if ($saldo >= $precio) {
        // Realizar el pedido
        $stmt = $pdo->prepare("INSERT INTO Pedidos_Comida (Hijo_Id, Menu_Id, Fecha_pedido, Estado) VALUES (?, ?, NOW(), 'Procesando')");
        if ($stmt->execute([$hijo_id, $menu_id])) {
            // Actualizar el saldo del usuario
            $stmt = $pdo->prepare("UPDATE Usuarios SET Saldo = Saldo - ? WHERE Id = ?");
            $stmt->execute([$precio, $usuario_id]);
            $success = "Pedido realizado con éxito.";
        } else {
            $error = "Error al realizar el pedido.";
        }
    } else {
        $error = "Saldo insuficiente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprar Viandas</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Comprar Viandas</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <form method="post" action="comprar_viandas.php">
        <label for="menu_id">Seleccione una vianda:</label>
        <select id="menu_id" name="menu_id" required>
            <option value="">Seleccione una vianda</option>
            <?php foreach ($menus as $menu) : ?>
                <option value="<?php echo htmlspecialchars($menu['Id']); ?>"><?php echo htmlspecialchars($menu['Nombre'] . " - " . $menu['Fecha_entrega'] . " - " . number_format($menu['Precio'], 2) . " ARS"); ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <button type="submit">Comprar Vianda</button>
    </form>
</body>
</html>
