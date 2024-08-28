<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Cocina</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .kpi-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .kpi {
            border: 1px solid #000;
            border-radius: 5px;
            padding: 20px;
            margin: 10px;
            text-align: center;
            width: 200px;
        }

        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-item {
            flex: 1 1 200px;
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Incluir jQuery -->
</head>

<body>
    <h1>Dashboard Cocina</h1>

    <form method="post" action="pedidos_colegios.php" class="filter-container">
        <div class="filter-item">
            <label for="fecha_entrega">Filtrar por Fecha de Entrega:</label>
            <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($fecha_filtro); ?>">
        </div>
        <div class="filter-item">
            <label for="colegio">Filtrar por Colegio:</label>
            <input type="text" id="colegio" name="colegio" value="<?php echo htmlspecialchars($colegio_filtro); ?>">
        </div>
        <div class="filter-item">
            <button type="submit" name="filtrar_fecha">Filtrar</button>
        </div>
        <div class="filter-item">
            <button type="submit" name="eliminar_filtro">Eliminar Filtro</button>
        </div>
    </form>

    <!-- Botón de Actualizar pedidos -->
    <button id="actualizar-pedidos" type="button">Actualizar pedidos</button>

    <h2>Total de Menús</h2>
    <div class="kpi-container" id="kpi-container">
        <?php
        $total_viandas = 0;
        foreach ($menus as $menu) :
            $total_viandas += $menu['Cantidad'];
        ?>
            <div class="kpi">
                <h3><?php echo htmlspecialchars($menu['MenuNombre']); ?></h3>
                <p>Cantidad: <?php echo htmlspecialchars($menu['Cantidad']); ?></p>
                <p>Fecha de entrega: <?php echo htmlspecialchars($menu['FechaEntrega']); ?></p>
            </div>
        <?php endforeach; ?>
        <div class="kpi">
            <h3>Total</h3>
            <p><?php echo $total_viandas; ?></p>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#actualizar-pedidos').click(function() {
                // Tomar los valores de los filtros actuales
                var fecha_entrega = $('#fecha_entrega').val();
                var colegio = $('#colegio').val();

                // Realizar la solicitud AJAX
                $.ajax({
                    type: 'POST',
                    url: 'actualizar_pedidos.php', // URL del archivo PHP que devuelve los datos actualizados
                    data: {
                        fecha_entrega: fecha_entrega,
                        colegio: colegio
                    },
                    success: function(response) {
                        var menus = JSON.parse(response); // Convertir la respuesta en un objeto JSON
                        var kpiContainer = $('#kpi-container');
                        kpiContainer.empty(); // Vaciar el contenedor actual

                        var totalViandas = 0;

                        // Recorrer los datos y crear los nuevos elementos
                        menus.forEach(function(menu) {
                            totalViandas += parseInt(menu.Cantidad);

                            var kpi = '<div class="kpi">';
                            kpi += '<h3>' + menu.MenuNombre + '</h3>';
                            kpi += '<p>Cantidad: ' + menu.Cantidad + '</p>';
                            kpi += '<p>Fecha de entrega: ' + menu.FechaEntrega + '</p>';
                            kpi += '</div>';

                            kpiContainer.append(kpi);
                        });

                        // Añadir el total de viandas
                        var totalKpi = '<div class="kpi">';
                        totalKpi += '<h3>Total</h3>';
                        totalKpi += '<p>' + totalViandas + '</p>';
                        totalKpi += '</div>';

                        kpiContainer.append(totalKpi);
                    }
                });
            });
        });
    </script>

</body>

</html>
