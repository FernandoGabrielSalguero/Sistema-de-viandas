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

$menus_por_dia = [];
foreach ($menus as $menu) {
    $menus_por_dia[$menu['Fecha_entrega']][] = $menu;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $menu_ids = $_POST['menu_ids'];
    $total_precio = 0;

    foreach ($menu_ids as $menu_id) {
        $stmt = $pdo->prepare("SELECT Precio FROM Menu WHERE Id = ?");
        $stmt->execute([$menu_id]);
        $precio = $stmt->fetch(PDO::FETCH_ASSOC)['Precio'];
        $total_precio += $precio;
    }

    // Verificar si el usuario tiene saldo suficiente
    $stmt = $pdo->prepare("SELECT Saldo FROM Usuarios WHERE Id = ?");
    $stmt->execute([$usuario_id]);
    $saldo = $stmt->fetch(PDO::FETCH_ASSOC)['Saldo'];

    if ($saldo >= $total_precio) {
        foreach ($menu_ids as $menu_id) {
            // Realizar el pedido
            $stmt = $pdo->prepare("INSERT INTO Pedidos_Comida (Hijo_Id, Menu_Id, Fecha_pedido, Estado) VALUES (?, ?, NOW(), 'Procesando')");
            if ($stmt->execute([$hijo_id, $menu_id])) {
                // Actualizar el saldo del usuario
                $stmt = $pdo->prepare("UPDATE Usuarios SET Saldo = Saldo - ? WHERE Id = ?");
                $stmt->execute([$total_precio, $usuario_id]);
                $success = "Pedido realizado con éxito.";
            } else {
                $error = "Error al realizar el pedido.";
            }
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
    <script>
        function actualizarTotal() {
            let total = 0;
            document.querySelectorAll('input[name="menu_ids[]"]:checked').forEach((checkbox) => {
                total += parseFloat(checkbox.dataset.precio);
            });
            document.getElementById('total').innerText = total.toFixed(2) + " ARS";
        }
    </script>
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
        <?php foreach ($menus_por_dia as $fecha => $menus) : ?>
            <h2><?php echo htmlspecialchars($fecha); ?></h2>
            <?php foreach ($menus as $menu) : ?>
                <div>
                    <label>
                        <input type="checkbox" name="menu_ids[]" value="<?php echo $menu['Id']; ?>" data-precio="<?php echo $menu['Precio']; ?>" onchange="actualizarTotal()">
                        <?php echo htmlspecialchars($menu['Nombre']) . " - " . number_format($menu['Precio'], 2) . " ARS"; ?>
                    </label>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <br>
        <p>Total: <span id="total">0.00 ARS</span></p>
        <button type="submit">Comprar Viandas</button>
    </form>
</body>
</html>
