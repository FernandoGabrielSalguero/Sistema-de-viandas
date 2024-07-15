<?php

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../includes/header_papas.php';
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'papas') {
    header("Location: ../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener saldo del usuario
$stmt = $pdo->prepare("SELECT Saldo FROM Usuarios WHERE Id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$saldo_disponible = $usuario['Saldo'];

// Obtener hijos del usuario
$stmt = $pdo->prepare("SELECT h.Id, h.Nombre FROM Hijos h JOIN Usuarios_Hijos uh ON h.Id = uh.Hijo_Id WHERE uh.Usuario_Id = ?");
$stmt->execute([$usuario_id]);
$hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener menús disponibles
$stmt = $pdo->prepare("SELECT m.Id, m.Nombre, m.Fecha_entrega, m.Precio FROM `Menú` m WHERE m.Estado = 'En venta'");
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

$menus_por_dia = [];
foreach ($menus as $menu) {
    $fecha_entrega = DateTime::createFromFormat('Y-m-d', $menu['Fecha_entrega'])->format('d/m/Y');
    $menus_por_dia[$fecha_entrega][] = $menu;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hijo_id = $_POST['hijo_id'];
    $menu_ids = $_POST['menu_ids'];
    $total_precio = 0;
    $fecha_entrega = '';

    foreach ($menu_ids as $menu_id) {
        $stmt = $pdo->prepare("SELECT Precio, Fecha_entrega FROM `Menú` WHERE Id = ?");
        $stmt->execute([$menu_id]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_precio += $menu['Precio'];
        $fecha_entrega = $menu['Fecha_entrega'];
    }

    // Verificar si el usuario tiene saldo suficiente
    if ($saldo_disponible >= $total_precio) {
        foreach ($menu_ids as $menu_id) {
            // Realizar el pedido
            $stmt = $pdo->prepare("INSERT INTO Pedidos_Comida (Hijo_Id, Menú_Id, Fecha_pedido, Estado, Fecha_entrega) VALUES (?, ?, NOW(), 'Procesando', ?)");
            if ($stmt->execute([$hijo_id, $menu_id, $fecha_entrega])) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <p>Saldo disponible: <?php echo number_format($saldo_disponible, 2); ?> ARS</p>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <form method="post" action="comprar_viandas.php">
        <label for="hijo_id">Seleccionar Hijo:</label>
        <select id="hijo_id" name="hijo_id" required>
            <option value="">Seleccione un hijo</option>
            <?php foreach ($hijos as $hijo) : ?>
                <option value="<?php echo $hijo['Id']; ?>"><?php echo htmlspecialchars($hijo['Nombre']); ?></option>
            <?php endforeach; ?>
        </select>
        <br><br>
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
