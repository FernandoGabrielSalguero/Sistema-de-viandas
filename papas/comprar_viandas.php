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

// Obtener hijos del usuario con su nivel educativo
$stmt = $pdo->prepare("SELECT h.Id, h.Nombre, h.Preferencias_Alimenticias, c.Nivel_Educativo 
                      FROM Hijos h 
                      JOIN Usuarios_Hijos uh ON h.Id = uh.Hijo_Id 
                      JOIN Cursos c ON h.Curso_Id = c.Id
                      WHERE uh.Usuario_Id = ?");
$stmt->execute([$usuario_id]);
$hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener menús disponibles agrupados por fecha
$stmt = $pdo->prepare("SELECT m.Id, m.Nombre, m.Fecha_entrega, m.Precio, m.Nivel_Educativo 
                      FROM `Menú` m 
                      WHERE m.Estado = 'En venta' 
                      ORDER BY m.Fecha_entrega ASC");
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

$menus_por_fecha = [];
foreach ($menus as $menu) {
    $menus_por_fecha[$menu['Fecha_entrega']][] = $menu;
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
        let menusPorFecha = <?php echo json_encode($menus_por_fecha); ?>;

        function actualizarMenus() {
            let hijoSeleccionado = document.getElementById('hijo_id').value;
            let nivelHijo = document.querySelector(`#hijo_id option[value="${hijoSeleccionado}"]`).dataset.nivel;
            let contenedorMenus = document.getElementById('menus_disponibles');

            contenedorMenus.innerHTML = ""; // Limpiar menús previos

            if (!hijoSeleccionado) {
                contenedorMenus.innerHTML = "<p>Seleccione un hijo para ver los menús disponibles.</p>";
                return;
            }

            let fechas = Object.keys(menusPorFecha);
            fechas.forEach(fecha => {
                let menusFiltrados = menusPorFecha[fecha].filter(menu => menu.Nivel_Educativo === nivelHijo);
                if (menusFiltrados.length > 0) {
                    let date = new Date(fecha);
                    let opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    let nombreFecha = date.toLocaleDateString('es-ES', opciones);

                    let fechaDiv = document.createElement('h2');
                    fechaDiv.innerText = nombreFecha;
                    contenedorMenus.appendChild(fechaDiv);

                    menusFiltrados.forEach(menu => {
                        let div = document.createElement('div');
                        div.innerHTML = `
                            <label>
                                <input type="checkbox" name="menu_ids[]" value="${menu.Id}" data-precio="${menu.Precio}" onchange="actualizarTotal()">
                                ${menu.Nombre} - ${menu.Precio} ARS
                            </label>
                        `;
                        contenedorMenus.appendChild(div);
                    });
                }
            });

            if (contenedorMenus.innerHTML === "") {
                contenedorMenus.innerHTML = "<p>No hay menús disponibles para este nivel educativo.</p>";
            }
        }

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
        <select id="hijo_id" name="hijo_id" required onchange="actualizarMenus()">
            <option value="">Seleccione un hijo</option>
            <?php foreach ($hijos as $hijo) : ?>
                <option value="<?php echo $hijo['Id']; ?>" data-nivel="<?php echo $hijo['Nivel_Educativo']; ?>">
                    <?php echo htmlspecialchars($hijo['Nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <h2>Menús Disponibles</h2>
        <div id="menus_disponibles">
            <p>Seleccione un hijo para ver los menús disponibles.</p>
        </div>

        <br>
        <p>Total: <span id="total">0.00 ARS</span></p>
        <button type="submit">Comprar Viandas</button>
    </form>
</body>
</html>
