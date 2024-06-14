<?php
include '../common/header.php';

$message = "";
$saldo_insuficiente = false;

// Obtener el saldo del padre
try {
    $stmt = $pdo->prepare("SELECT saldo FROM parents WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $parent = $stmt->fetch(PDO::FETCH_ASSOC);
    $parent_saldo = $parent['saldo'];
} catch (Exception $e) {
    error_log($e->getMessage());
    $parent_saldo = 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_menu'])) {
    try {
        $parent_id = $_SESSION['user_id'];
        $child_id = $_POST['child_id'];
        $menus_selected = isset($_POST['menus']) ? $_POST['menus'] : [];

        $total_price = 0;
        foreach ($menus_selected as $menu_id) {
            if ($menu_id != 'none') {
                // Obtener el precio del menú seleccionado
                $stmt = $pdo->prepare("SELECT price FROM menus WHERE id = ?");
                $stmt->execute([$menu_id]);
                $menu = $stmt->fetch(PDO::FETCH_ASSOC);
                $menu_price = $menu['price'];
                $total_price += $menu_price;
            }
        }

        if ($parent_saldo >= $total_price) {
            // Descontar el saldo
            $new_saldo = $parent_saldo - $total_price;
            $stmt = $pdo->prepare("UPDATE parents SET saldo = ? WHERE id = ?");
            $stmt->execute([$new_saldo, $parent_id]);

            // Insertar los pedidos en la base de datos
            foreach ($menus_selected as $menu_id) {
                if ($menu_id != 'none') {
                    $stmt = $pdo->prepare("SELECT date FROM menus WHERE id = ?");
                    $stmt->execute([$menu_id]);
                    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
                    $order_date = $menu['date'];

                    $stmt = $pdo->prepare("INSERT INTO orders (parent_id, child_id, menu_id, order_date) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$parent_id, $child_id, $menu_id, $order_date]);
                }
            }

            $message = "El pedido ha sido realizado exitosamente.";
        } else {
            $saldo_insuficiente = true;
            $faltante = $total_price - $parent_saldo;
            $message = "Saldo insuficiente. Necesitas $" . number_format($faltante, 2) . " más para realizar este pedido.";
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $message = "Error: No se pudo realizar el pedido.";
    }
}

// Obtener los hijos del padre
try {
    $stmt = $pdo->prepare("SELECT * FROM children WHERE parent_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $children = [];
}

// Obtener los menús disponibles, agrupados por fecha
try {
    $stmt = $pdo->prepare("SELECT * FROM menus ORDER BY date DESC");
    $stmt->execute();
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $menus_by_date = [];
    foreach ($menus as $menu) {
        $menus_by_date[$menu['date']][] = $menu;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $menus_by_date = [];
}
?>

<div class="container">
    <div id="toast" class="toast"><?php echo $message; ?>
        <?php if ($saldo_insuficiente): ?>
            <button onclick="window.location.href='../parents/recharge.php'">Recargar Saldo</button>
        <?php endif; ?>
    </div>
    <form action="order_menu.php" method="post" onsubmit="return checkTotal()">
        <h2>Realizar Pedido</h2>
        <p>Saldo disponible: $<?php echo number_format($parent_saldo, 2); ?></p>
        <label for="child_id">Hijo:</label>
        <select id="child_id" name="child_id" required>
            <?php foreach ($children as $child): ?>
                <option value="<?php echo $child['id']; ?>"><?php echo htmlspecialchars($child['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <?php foreach ($menus_by_date as $date => $menus): ?>
            <fieldset>
                <legend><?php echo date('d/m/Y', strtotime($date)); ?></legend>
                <div>
                    <input type="radio" id="menu_none_<?php echo $date; ?>" name="menus[<?php echo $date; ?>]" value="none" data-price="0" checked>
                    <label for="menu_none_<?php echo $date; ?>">Sin Vianda</label>
                </div>
                <?php foreach ($menus as $menu): ?>
                    <div>
                        <input type="radio" id="menu_<?php echo $menu['id']; ?>" name="menus[<?php echo $date; ?>]" value="<?php echo $menu['id']; ?>" data-price="<?php echo $menu['price']; ?>">
                        <label for="menu_<?php echo $menu['id']; ?>"><?php echo htmlspecialchars($menu['name'] . ' - $' . $menu['price']); ?></label>
                    </div>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <button type="submit" name="order_menu">Realizar Pedido ($<span id="total_button">0.00</span>)</button>
    </form>
    
    <h2>Pedidos Realizados</h2>
    <table id="ordersTable">
        <thead>
            <tr>
                <th onclick="sortTable(0)">Hijo</th>
                <th onclick="sortTable(1)">Menú</th>
                <th onclick="sortTable(2)">Fecha del Pedido</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Obtener los pedidos realizados
            try {
                $stmt = $pdo->prepare("
                    SELECT orders.id, children.name as child_name, menus.name as menu_name, orders.order_date
                    FROM orders
                    JOIN children ON orders.child_id = children.id
                    JOIN menus ON orders.menu_id = menus.id
                    WHERE orders.parent_id = ?
                    ORDER BY orders.order_date DESC
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log($e->getMessage());
                $orders = [];
            }
            ?>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['child_name']); ?></td>
                <td><?php echo htmlspecialchars($order['menu_name']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                <td>
                    <a href="update_order.php?id=<?php echo $order['id']; ?>">Actualizar</a>
                    <a href="cancel_order.php?id=<?php echo $order['id']; ?>" onclick="return confirm('¿Estás seguro de que quieres cancelar este pedido?')">Cancelar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function sortTable(columnIndex) {
    const table = document.getElementById('ordersTable');
    let rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    switching = true;
    dir = "asc"; 
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[columnIndex];
            y = rows[i].getElementsByTagName("TD")[columnIndex + 1];
            if (dir === "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir === "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++; 
        } else {
            if (switchcount === 0 && dir === "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}

function calculateTotal() {
    const radios = document.querySelectorAll('input[type="radio"]:checked');
    let total = 0;
    radios.forEach(radio => {
        total += parseFloat(radio.getAttribute('data-price'));
    });
    document.getElementById('total_button').textContent = total.toFixed(2);
}

document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', calculateTotal);
});

function checkTotal() {
    const total = parseFloat(document.getElementById('total_button').textContent);
    return total >= 0;
}

function showToast(message) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.className = "toast show";
    setTimeout(function() {
        toast.className = toast.className.replace("show", "");
    }, 5000);
}

// Mostrar el mensaje de toast si hay uno
<?php if (isset($message) && $message): ?>
    showToast("<?php echo $message; ?>");
<?php endif; ?>
</script>
</body>
</html>