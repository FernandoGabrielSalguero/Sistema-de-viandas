<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_cocina.php';
include '../includes/db.php';

// Variables de filtro
$fecha_filtro = isset($_GET['fecha_entrega']) ? $_GET['fecha_entrega'] : '';
$colegio_filtro = isset($_GET['colegio']) ? $_GET['colegio'] : '';

// -------------------- OBTENER VIANDAS POR NIVEL --------------------
$query_niveles = "
    SELECT 
        cu.Nivel AS Nivel,
        COUNT(*) AS Cantidad
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    WHERE pc.Estado = 'Procesando'
";
$params_niveles = [];

if (!empty($fecha_filtro)) {
    $query_niveles .= " AND pc.Fecha_entrega = ?";
    $params_niveles[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_niveles .= " AND h.Colegio_Id = ?";
    $params_niveles[] = $colegio_filtro;
}

$query_niveles .= " GROUP BY cu.Nivel";
$stmt = $pdo->prepare($query_niveles);
$stmt->execute($params_niveles);
$niveles_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -------------------- OBTENER DETALLE DE PEDIDOS --------------------
$query_pedidos = "
    SELECT 
        pc.Id AS PedidoID,
        h.Nombre AS Alumno,
        cu.Nivel AS Nivel,
        cu.Nombre AS Curso,
        m.Nombre AS Menu,
        pc.Fecha_entrega,
        pc.Estado
    FROM Pedidos_Comida pc
    JOIN Hijos h ON pc.Hijo_Id = h.Id
    JOIN Cursos cu ON h.Curso_Id = cu.Id
    JOIN Menú m ON pc.Menú_Id = m.Id
    WHERE pc.Estado = 'Procesando'
";
$params_pedidos = [];

if (!empty($fecha_filtro)) {
    $query_pedidos .= " AND pc.Fecha_entrega = ?";
    $params_pedidos[] = $fecha_filtro;
}
if (!empty($colegio_filtro)) {
    $query_pedidos .= " AND h.Colegio_Id = ?";
    $params_pedidos[] = $colegio_filtro;
}

$stmt = $pdo->prepare($query_pedidos);
$stmt->execute($params_pedidos);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar pedidos por nivel
$pedidos_por_nivel = [];
foreach ($pedidos as $pedido) {
    $nivel = $pedido['Nivel'];
    if (!isset($pedidos_por_nivel[$nivel])) {
        $pedidos_por_nivel[$nivel] = [];
    }
    $pedidos_por_nivel[$nivel][] = $pedido;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Cocina</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .table-container {
            margin-top: 20px;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            margin: 10% auto;
            width: 60%;
            border-radius: 10px;
        }
        .close {
            float: right;
            font-size: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Dashboard Cocina</h1>
    
    <h2>Total de Viandas por Nivel</h2>
    <div class="table-container">
        <table>
            <tr>
                <th>Nivel</th>
                <th>Cantidad</th>
                <th>Acción</th>
            </tr>
            <?php foreach ($niveles_data as $nivel) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($nivel['Nivel']); ?></td>
                    <td><?php echo htmlspecialchars($nivel['Cantidad']); ?></td>
                    <td>
                        <button class="btn" onclick="openModal('<?php echo htmlspecialchars($nivel['Nivel']); ?>')">
                            Ver Detalle
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- MODAL PARA DETALLE DE PEDIDOS -->
    <div id="detalleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modal-title"></h2>
            <table id="detalle-table">
                <tr>
                    <th>ID Pedido</th>
                    <th>Alumno</th>
                    <th>Curso</th>
                    <th>Menú</th>
                    <th>Fecha de Entrega</th>
                    <th>Estado</th>
                </tr>
            </table>
            <button class="btn" onclick="descargarCSV()">Descargar CSV</button>
        </div>
    </div>

    <script>
        const pedidosPorNivel = <?php echo json_encode($pedidos_por_nivel); ?>;

        function openModal(nivel) {
            const modal = document.getElementById("detalleModal");
            const modalTitle = document.getElementById("modal-title");
            const table = document.getElementById("detalle-table");

            modal.style.display = "block";
            modalTitle.innerText = "Detalle de " + nivel;

            // Limpiar la tabla (excepto los encabezados)
            while (table.rows.length > 1) {
                table.deleteRow(1);
            }

            // Agregar filas con los datos
            if (pedidosPorNivel[nivel]) {
                pedidosPorNivel[nivel].forEach(pedido => {
                    let row = table.insertRow();
                    row.insertCell(0).innerText = pedido.PedidoID;
                    row.insertCell(1).innerText = pedido.Alumno;
                    row.insertCell(2).innerText = pedido.Curso;
                    row.insertCell(3).innerText = pedido.Menu;
                    row.insertCell(4).innerText = pedido.Fecha_entrega;
                    row.insertCell(5).innerText = pedido.Estado;
                });
            }
        }

        function closeModal() {
            document.getElementById("detalleModal").style.display = "none";
        }

        function descargarCSV() {
            let csvContent = "ID Pedido,Alumno,Curso,Menú,Fecha de Entrega,Estado\n";
            document.querySelectorAll("#detalle-table tr").forEach((row, index) => {
                if (index > 0) { 
                    let rowData = Array.from(row.cells).map(cell => cell.innerText).join(",");
                    csvContent += rowData + "\n";
                }
            });

            let blob = new Blob([csvContent], { type: "text/csv" });
            let link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "detalle_pedidos.csv";
            link.click();
        }
    </script>
</body>
</html>
