<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'Usuario') {
    header('Location: /login.php');
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Obtener hijos del usuario
$hijos = $pdo->prepare("SELECT * FROM hijos WHERE usuario_id = :usuario_id");
$hijos->execute(['usuario_id' => $user_id]);
$lista_hijos = $hijos->fetchAll(PDO::FETCH_ASSOC);

// Obtener menús disponibles
$menus = $pdo->query("SELECT * FROM menus WHERE fecha >= CURDATE()")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hijo_id = $_POST['hijo_id'];
    $menu_id = $_POST['menu_id'];
    $notas = $_POST['notas'];
    $user_id = $_SESSION['user_id'];
    
    // Verificar saldo
    $stmt = $pdo->prepare("SELECT saldo FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $usuario = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT precio FROM menus WHERE id = :id");
    $stmt->execute(['id' => $menu_id]);
    $menu = $stmt->fetch();
    
    if ($usuario['saldo'] >= $menu['precio']) {
        // Crear pedido
        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, hijo_id, menu_id, fecha_pedido, notas, estado) VALUES (:usuario_id, :hijo_id, :menu_id, NOW(), :notas, 'Pendiente')");
        $stmt->execute(['usuario_id' => $user_id, 'hijo_id' => $hijo_id, 'menu_id' => $menu_id, 'notas' => $notas]);
        
        // Actualizar saldo
        $nuevo_saldo = $usuario['saldo'] - $menu['precio'];
        $stmt = $pdo->prepare("UPDATE usuarios SET saldo = :saldo WHERE id = :id");
        $stmt->execute(['saldo' => $nuevo_saldo, 'id' => $user_id]);
        
        $mensaje = "Pedido realizado exitosamente.";
    } else {
        $error = "Saldo insuficiente.";
    }
}
?>
<div class="manage-orders">
    <h1>Realizar Pedido</h1>
    <?php if (isset($mensaje)) { echo "<p>$mensaje</p>"; } ?>
    <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    <form action="manage_orders.php" method="POST">
        <label for="hijo_id">Seleccionar Hijo</label>
        <select name="hijo_id" id="hijo_id">
            <?php foreach ($lista_hijos as $hijo) { ?>
            <option value="<?php echo $hijo['id']; ?>"><?php echo $hijo['nombre'] . ' ' . $hijo['apellido']; ?></option>
            <?php } ?>
        </select>
        <label for="menu_id">Seleccionar Menú</label>
        <select name="menu_id" id="menu_id">
            <?php foreach ($menus as $menu) { ?>
            <option value="<?php echo $menu['id']; ?>"><?php echo $menu['nombre'] . ' - $' . $menu['precio']; ?></option>
            <?php } ?>
        </select>
        <label for="notas">Notas</label>
        <input type="text" id="notas" name="notas">
        <button type="submit">Realizar Pedido</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
