<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/header_admin.php';
include '../includes/db.php';

// Procesar el formulario cuando se envíe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_menu'])) {
    $nombre_menu = $_POST['nombre_menu'];
    $fecha_entrega = $_POST['fecha_entrega'];
    $fecha_hora_compra = $_POST['fecha_hora_compra'];
    $fecha_hora_cancelacion = $_POST['fecha_hora_cancelacion'];
    $precio = $_POST['precio'];
    $estado = $_POST['estado'];
    $nivel_educativo = $_POST['nivel_educativo'];

    // Validar que todos los campos estén llenos
    if (empty($nombre_menu) || empty($fecha_entrega) || empty($fecha_hora_compra) || empty($fecha_hora_cancelacion) || empty($precio) || empty($estado)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Insertar el nuevo menú en la base de datos
        $stmt = $pdo->prepare("INSERT INTO Menú (Nombre, Fecha_entrega, Fecha_hora_compra, Fecha_hora_cancelacion, Precio, Estado, Nivel_Educativo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$nombre_menu, $fecha_entrega, $fecha_hora_compra, $fecha_hora_cancelacion, $precio, $estado, $nivel_educativo])) {
            $success = "Menú creado con éxito.";
        } else {
            $error = "Hubo un error al crear el menú.";
        }
    }
}

// Obtener el total de menús para paginación
$total_menus = $pdo->query("SELECT COUNT(*) FROM Menú")->fetchColumn();

// Definir el número de menús por página
$menus_por_pagina = 25;

// Obtener la página actual
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $menus_por_pagina;

// Obtener menús con límite y orden descendente por ID
$stmt = $pdo->prepare("SELECT Id, Nombre, Fecha_entrega, Fecha_hora_compra, Fecha_hora_cancelacion, Precio, Estado, Nivel_Educativo FROM Menú ORDER BY Id DESC LIMIT :inicio, :menus_por_pagina");
$stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindParam(':menus_por_pagina', $menus_por_pagina, PDO::PARAM_INT);
$stmt->execute();
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Procesar la actualización del menú
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_menu'])) {
    $menu_id = $_POST['menu_id'];
    $nombre_menu = $_POST['nombre_menu'];
    $fecha_entrega = $_POST['fecha_entrega'];
    $fecha_hora_compra = $_POST['fecha_hora_compra'];
    $fecha_hora_cancelacion = $_POST['fecha_hora_cancelacion'];
    $precio = $_POST['precio'];
    $estado = $_POST['estado'];
    $nivel_educativo = $_POST['nivel_educativo'];

    // Validar que todos los campos estén llenos
    if (empty($nombre_menu) || empty($fecha_entrega) || empty($fecha_hora_compra) || empty($fecha_hora_cancelacion) || empty($precio) || empty($estado) || empty($nivel_educativo)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Actualizar el menú en la base de datos
        $stmt = $pdo->prepare("UPDATE Menú SET Nombre = ?, Fecha_entrega = ?, Fecha_hora_compra = ?, Fecha_hora_cancelacion = ?, Precio = ?, Estado = ?, Nivel_Educativo = ? WHERE Id = ?");
        if ($stmt->execute([$nombre_menu, $fecha_entrega, $fecha_hora_compra, $fecha_hora_cancelacion, $precio, $estado, $nivel_educativo, $menu_id])) {

            $success = "Menú actualizado con éxito.";
        } else {
            $error = "Hubo un error al actualizar el menú.";
        }
    }
}

// // Obtener todos los menús
// $stmt = $pdo->prepare("SELECT * FROM Menú");
// $stmt->execute();
// $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
// 
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Alta de Menú</title>
    <link rel="stylesheet" href="../css/styles_alta_menu.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <h1>Alta de Menú</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <form method="post" action="alta_menu.php">
        <label for="nombre_menu">Nombre del Menú</label>
        <input type="text" id="nombre_menu" name="nombre_menu" required>

        <label for="fecha_entrega">Fecha de Entrega</label>
        <input type="date" id="fecha_entrega" name="fecha_entrega" required>

        <label for="fecha_hora_compra">Fecha y Hora Límite de Compra</label>
        <input type="datetime-local" id="fecha_hora_compra" name="fecha_hora_compra" required>

        <label for="fecha_hora_cancelacion">Fecha y Hora Límite de Cancelación</label>
        <input type="datetime-local" id="fecha_hora_cancelacion" name="fecha_hora_cancelacion" required>

        <label for="precio">Precio</label>
        <input type="number" id="precio" name="precio" step="0.01" required>

        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
            <option value="En venta">En venta</option>
            <option value="Sin stock">Sin stock</option>
        </select>

        <label for="nivel_educativo">Nivel Educativo</label>
        <select id="nivel_educativo" name="nivel_educativo" required>
            <option value="Inicial">Nivel Inicial</option>
            <option value="Primaria">Primaria</option>
            <option value="Secundaria">Secundaria</option>
        </select>

        <button type="submit" name="crear_menu">Crear Menú</button>
    </form>

    <h2>Lista de Menús</h2>
    <div class="table-container">
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Fecha de Entrega</th>
                <th>Fecha y Hora Límite de Compra</th>
                <th>Fecha y Hora Límite de Cancelación</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Nivel Educativo</th>
                <th>Acción</th>
            </tr>
            <?php foreach ($menus as $menu) : ?>
                <tr class="estado-<?php echo strtolower(str_replace(' ', '-', $menu['Estado'])); ?>">
                    <form method="post" action="alta_menu.php">
                        <td><?php echo htmlspecialchars($menu['Id']); ?></td>
                        <td><input type="text" name="nombre_menu" value="<?php echo htmlspecialchars($menu['Nombre']); ?>" required></td>
                        <td><input type="date" name="fecha_entrega" value="<?php echo htmlspecialchars($menu['Fecha_entrega']); ?>" required></td>
                        <td><input type="datetime-local" name="fecha_hora_compra" value="<?php echo date('Y-m-d\TH:i', strtotime($menu['Fecha_hora_compra'])); ?>" required></td>
                        <td><input type="datetime-local" name="fecha_hora_cancelacion" value="<?php echo date('Y-m-d\TH:i', strtotime($menu['Fecha_hora_cancelacion'])); ?>" required></td>
                        <td><input type="number" name="precio" step="0.01" value="<?php echo htmlspecialchars($menu['Precio']); ?>" required></td>
                        <td>
                            <select name="estado" required>
                                <option value="En venta" <?php echo ($menu['Estado'] == 'En venta') ? 'selected' : ''; ?>>En venta</option>
                                <option value="Sin stock" <?php echo ($menu['Estado'] == 'Sin stock') ? 'selected' : ''; ?>>Sin stock</option>
                            </select>
                        </td>
                        <td>
                            <select name="nivel_educativo" required>
                                <option value="Inicial" <?php echo ($menu['Nivel_Educativo'] == 'Inicial') ? 'selected' : ''; ?>>Inicial</option>
                                <option value="Primaria" <?php echo ($menu['Nivel_Educativo'] == 'Primaria') ? 'selected' : ''; ?>>Primaria</option>
                                <option value="Secundaria" <?php echo ($menu['Nivel_Educativo'] == 'Secundaria') ? 'selected' : ''; ?>>Secundaria</option>
                            </select>

                        </td>
                        <td>
                            <input type="hidden" name="menu_id" value="<?php echo htmlspecialchars($menu['Id']); ?>">
                            <button type="submit" name="actualizar_menu">Actualizar</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Bloque de paginación debe estar fuera de la tabla -->
    <div class="pagination">
        <?php
        $total_paginas = ceil($total_menus / $menus_por_pagina);
        for ($i = 1; $i <= $total_paginas; $i++) {
            echo "<a href='?pagina=$i' class='" . ($i == $pagina_actual ? 'active' : '') . "'>$i</a>";
        }
        ?>
    </div>

</body>

</html>