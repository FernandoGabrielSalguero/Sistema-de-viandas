<?php
include '../common/header.php';

// Verificar si el usuario tiene rol de representante de la escuela
if ($_SESSION['role'] !== 'school') {
    header("Location: ../login.php");
    exit;
}

$school_rep_id = $_SESSION['user_id'];

// Obtener los pedidos realizados por los padres de la escuela del representante
try {
    $stmt = $pdo->prepare("
        SELECT orders.id, parents.name as parent_name, children.name as child_name, menus.name as menu_name, menus.date as menu_date
        FROM orders
        JOIN children ON orders.child_id = children.id
        JOIN parents ON orders.parent_id = parents.id
        JOIN courses ON children.course_id = courses.id
        JOIN schools ON courses.school_id = schools.id
        JOIN menus ON orders.menu_id = menus.id
        WHERE schools.rep_id = ?
        ORDER BY menus.date DESC, parents.name
    ");
    $stmt->execute([$school_rep_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
    $orders = [];
}
?>

<div class="container">
    <h2>Perfil del Representante de la Escuela</h2>

    <h3>Pedidos Realizados</h3>
    <input type="text" id="filterInput" onkeyup="filterTable()" placeholder="Buscar por nombre de padre, hijo o menú..">
    <table id="ordersTable">
        <thead>
            <tr>
                <th>Padre</th>
                <th>Hijo</th>
                <th>Menú</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['parent_name']); ?></td>
                <td><?php echo htmlspecialchars($order['child_name']); ?></td>
                <td><?php echo htmlspecialchars($order['menu_name']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($order['menu_date'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.container {
    padding: 20px;
}

input[type="text"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 16px;
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
</style>

<script>
function filterTable() {
    var input, filter, table, tr, td, i, j, txtValue;
    input = document.getElementById('filterInput');
    filter = input.value.toUpperCase();
    table = document.getElementById('ordersTable');
    tr = table.getElementsByTagName('tr');

    for (i = 1; i < tr.length; i++) {
        tr[i].style.display = 'none';
        td = tr[i].getElementsByTagName('td');
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = '';
                    break;
                }
            }
        }
    }
}
</script>

<?php
include '../common/footer.php';
?>
