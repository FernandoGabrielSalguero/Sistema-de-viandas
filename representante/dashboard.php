<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Representante</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style_representante.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="dashboard.php">Inicio</a></li>
            <li><a href="logout.php">Salir</a></li>
        </ul>
    </nav>

    <h1>Bienvenido, Representante</h1>
    <h2>Historial de Pedidos de Viandas</h2>

    <form method="get" action="dashboard.php" class="filters">
        <div class="filter-item">
            <label for="curso_id">Filtrar por Curso:</label>
            <select id="curso_id" name="curso_id">
                <option value="">Todos</option>
                <?php foreach ($cursos as $curso) : ?>
                    <option value="<?php echo $curso['Id']; ?>" <?php if ($filtro_curso == $curso['Id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($curso['Nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-item">
            <label for="fecha_entrega">Filtrar por Fecha de Entrega:</label>
            <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($filtro_fecha); ?>">
        </div>

        <div class="filter-item">
            <button type="submit">Filtrar</button>
        </div>
    </form>

    <table>
        <tr>
            <th>ID Pedido</th>
            <th>Hijo</th>
            <th>Menú</th>
            <th>Fecha de Entrega</th>
            <th>Estado</th>
            <th>Preferencias Alimenticias</th>
        </tr>
        <?php foreach ($pedidos_viandas as $pedido) : ?>
        <tr>
            <td><?php echo htmlspecialchars($pedido['Pedido_Id']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Hijo']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Menu']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Fecha_entrega'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($pedido['Estado']); ?></td>
            <td><?php echo htmlspecialchars($pedido['Preferencias_alimenticias']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
