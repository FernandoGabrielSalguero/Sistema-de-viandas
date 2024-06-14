<?php
include '../common/header.php';

// Verificar si el usuario es un administrador
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener la cantidad de usuarios registrados
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_users FROM parents");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
} catch (Exception $e) {
    error_log($e->getMessage());
    $total_users = 0;
}

// Obtener la cantidad de pedidos realizados
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_orders FROM orders");
    $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
} catch (Exception $e) {
    error_log($e->getMessage());
    $total_orders = 0;
}

// Obtener la cantidad de dinero aprobado en saldo
try {
    $stmt = $pdo->query("SELECT SUM(amount) AS total_approved FROM recharges WHERE status = 'approved'");
    $total_approved = $stmt->fetch(PDO::FETCH_ASSOC)['total_approved'];
} catch (Exception $e) {
    error_log($e->getMessage());
    $total_approved = 0;
}

// Obtener la cantidad de dinero aún no aprobado en saldo
try {
    $stmt = $pdo->query("SELECT SUM(amount) AS total_pending FROM recharges WHERE status = 'pending'");
    $total_pending = $stmt->fetch(PDO::FETCH_ASSOC)['total_pending'];
} catch (Exception $e) {
    error_log($e->getMessage());
    $total_pending = 0;
}

// Obtener los usuarios que no han realizado pedidos
try {
    $stmt = $pdo->query("
        SELECT parents.name, parents.email 
        FROM parents 
        LEFT JOIN orders ON parents.id = orders.parent_id 
        WHERE orders.id IS NULL
    ");
    $no_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $no_orders = [];
}

// Obtener todos los menús
try {
    $stmt = $pdo->prepare("SELECT * FROM menus ORDER BY date DESC");
    $stmt->execute();
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $menus = [];
}
?>

<div class="container">
    <h2>Panel de Control del Administrador</h2>

    <div class="admin-kpi">
        <div class="kpi-card">
            <h3>Usuarios Registrados</h3>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="kpi-card">
            <h3>Pedidos Realizados</h3>
            <p id="total_orders_kpi"><?php echo $total_orders; ?></p>
            <label for="order_filter_date">Filtrar por fecha:</label>
            <input type="date" id="order_filter_date" name="order_filter_date" onchange="filterOrdersByDate()">
        </div>
        <div class="kpi-card">
            <h3>Saldo Aprobado</h3>
            <p>$<?php echo number_format($total_approved, 2); ?></p>
        </div>
        <div class="kpi-card">
            <h3>Saldo Pendiente</h3>
            <p>$<?php echo number_format($total_pending, 2); ?></p>
        </div>
    </div>
    
    <div class="admin-section">
        <h3>Usuarios que No Han Realizado Pedidos</h3>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($no_orders as $parent): ?>
                <tr>
                    <td><?php echo htmlspecialchars($parent['name']); ?></td>
                    <td><?php echo htmlspecialchars($parent['email']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-section">
        <h3>Menús</h3>
        <table id="menusTable">
            <thead>
                <tr>
                    <th onclick="sortTable(0)">Nombre</th>
                    <th onclick="sortTable(1)">Fecha</th>
                    <th onclick="sortTable(2)">Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $menu): ?>
                <tr>
                    <td><?php echo htmlspecialchars($menu['name']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($menu['date'])); ?></td>
                    <td><?php echo number_format($menu['price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.container {
    padding: 20px;
}
.admin-kpi {
    display: flex;
    justify-content: space-around;
    margin-bottom: 40px;
    flex-wrap: wrap; /* Para hacer el contenido responsive */
}
.kpi-card {
    background-color: #f4f4f4;
    border: 1px solid #ddd;
    padding: 20px;
    text-align: center;
    width: 18%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin: 10px; /* Para evitar que los elementos se solapen */
}
.kpi-card h3 {
    margin-top: 0;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table, th, td {
    border: 1px solid #ddd;
}
th, td {
    padding: 8px;
    text-align: left;
}
th {
    background-color: #f4f4f4;
}
.admin-section {
    margin-bottom: 40px;
}
</style>

<script>
function sortTable(columnIndex) {
    const table = document.getElementById('menusTable');
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

function filterOrdersByDate() {
    const filterDate = document.getElementById('order_filter_date').value;
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'filter_orders.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (this.status === 200) {
            const response = JSON.parse(this.responseText);
            document.getElementById('ordersTableBody').innerHTML = response.html;
            document.getElementById('total_orders_kpi').textContent = response.total_orders;
        }
    };
    xhr.send('filter_date=' + filterDate);
}
</script>

<?php
include '../common/footer.php';
?>
