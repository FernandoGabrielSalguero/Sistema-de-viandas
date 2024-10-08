<?php
session_start();
include '../includes/header_cuyo_placa.php';
include '../includes/db.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cuyo_placa') {
    header("Location: ../index.php");
    exit();
}

$fecha_seleccionada = '';
$pedidos_del_dia = [];
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha_seleccionada = $_POST['fecha'];
    $hora_actual = date('H:i');
    $hora_limite = '11:00';
    $fecha_hoy = date('Y-m-d');
    $es_mismo_dia = ($fecha_seleccionada == $fecha_hoy);

    if ($es_mismo_dia && $hora_actual >= $hora_limite) {
        $mensaje = "No se pueden actualizar los pedidos después de las 11:00 AM del día seleccionado.";
    } else {
        // Obtener los pedidos del día seleccionado
        $stmt = $pdo->prepare("SELECT d.id, d.planta, d.menu, d.turno, d.cantidad 
                               FROM Detalle_Pedidos_Cuyo_Placa d 
                               JOIN Pedidos_Cuyo_Placa p ON d.pedido_id = p.id 
                               WHERE p.fecha = ? 
                               ORDER BY d.planta, d.turno, d.menu");
        $stmt->execute([$fecha_seleccionada]);
        $pedidos_del_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (isset($_POST['actualizar'])) {
            $hora_actual = date('H:i');
            $fecha_actual = date('Y-m-d');

            // Verificar si el pedido está dentro del límite de tiempo permitido
            if ($fecha_seleccionada == $fecha_actual && $hora_actual >= $hora_limite) {
                // Mostrar el modal y no actualizar
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const modalText = document.getElementById('modalText');
                        modalText.innerText = 'Este pedido se podía actualizar hasta el $fecha_seleccionada a las 10hs.';
                        document.getElementById('modal').style.display = 'block';
                    });
                </script>";
            } else {
                // Actualizar los pedidos según los datos enviados desde el formulario
                foreach ($pedidos_del_dia as $pedido) {
                    $pedido_id = $pedido['id'];
                    if (isset($_POST['cantidad_' . $pedido_id])) {
                        $nuevo_valor = $_POST['cantidad_' . $pedido_id];
                        $stmt = $pdo->prepare("UPDATE Detalle_Pedidos_Cuyo_Placa SET cantidad = ? WHERE id = ?");
                        $stmt->execute([$nuevo_valor, $pedido_id]);
                    }
                }
                $mensaje = "Pedidos actualizados correctamente.";
                echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const modalText = document.getElementById('modalText');
                    modalText.innerText = 'Pedido actualizado';
                    document.getElementById('modal').style.display = 'block';
                    });
                    </script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Pedidos de Viandas</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
            color: #343a40;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-right: 10px;
            color: #343a40;
        }

        input[type="date"] {
            padding: 8px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            font-size: 1em;
        }

        table {
            width: 100%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        table, th, td {
            border: 1px solid #e9ecef;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        td {
            background-color: #ffffff;
        }

        input[type="number"] {
            width: 60px;
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            text-align: center;
        }

        button {
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        /* Estilos del Modal */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 300px;
            text-align: center;
            border-radius: 10px;
        }

        .modal-content p {
            margin-bottom: 20px;
            font-size: 1.2em;
            color: #343a40;
        }

        .close, .modal-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }

        .close:hover, .modal-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Modificar Pedidos de Viandas</h1>

    <?php if ($mensaje): ?>
        <p class="<?php echo strpos($mensaje, 'No') === false ? 'mensaje' : 'error'; ?>">
            <?php echo $mensaje; ?>
        </p>
    <?php endif; ?>

    <form method="post" action="mod_pedidos_viandas_cuyo.php">
        <label for="fecha">Fecha:</label>
        <input type="date" id="fecha" name="fecha" required value="<?php echo htmlspecialchars($fecha_seleccionada); ?>">
        <button type="submit">Buscar</button>
    </form>

    <?php if ($pedidos_del_dia): ?>
        <form method="post" action="mod_pedidos_viandas_cuyo.php" id="formActualizar">
            <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($fecha_seleccionada); ?>">
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">Planta</th>
                        <th colspan="3">Mañana</th>
                        <th colspan="3">Tarde</th>
                        <th colspan="2">Noche</th>
                    </tr>
                    <tr>
                        <th>Desayuno día siguiente</th>
                        <th>Almuerzo Caliente</th>
                        <th>Refrigerio sandwich almuerzo</th>
                        <th>Media tarde</th>
                        <th>Cena caliente</th>
                        <th>Refrigerio sandwich cena</th>
                        <th>Desayuno noche</th>
                        <th>Sandwich noche</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $plantas = ['Aglomerado', 'Revestimiento', 'Impregnacion', 'Muebles', 'Transporte (Revestimiento)'];
                    $menus = [
                        'Desayuno día siguiente' => 'Mañana',
                        'Almuerzo Caliente' => 'Mañana',
                        'Refrigerio sandwich almuerzo' => 'Mañana',
                        'Media tarde' => 'Tarde',
                        'Cena caliente' => 'Tarde',
                        'Refrigerio sandwich cena' => 'Tarde',
                        'Desayuno noche' => 'Noche',
                        'Sandwich noche' => 'Noche'
                    ];

                    foreach ($plantas as $planta) : ?>
                        <tr>
                            <td><?php echo $planta; ?></td>
                            <?php foreach ($menus as $menu => $turno) : 
                                $cantidad = 0;
                                $pedido_id = null;
                                foreach ($pedidos_del_dia as $pedido) {
                                    if ($pedido['planta'] == $planta && $pedido['menu'] == $menu && $pedido['turno'] == $turno) {
                                        $cantidad = $pedido['cantidad'];
                                        $pedido_id = $pedido['id'];
                                    }
                                }
                            ?>
                                <td>
                                    <?php if ($pedido_id !== null): ?>
                                        <input type="number" name="cantidad_<?php echo htmlspecialchars($pedido_id); ?>" value="<?php echo htmlspecialchars($cantidad); ?>" min="0" id="input_<?php echo htmlspecialchars($pedido_id); ?>">
                                    <?php else: ?>
                                        <input type="number" value="0" readonly>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="actualizar" id="actualizarBtn">Actualizar pedido</button>
        </form>
    <?php endif; ?>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <p id="modalText"></p>
            <button class="modal-button" onclick="redirigirAlDashboard()">Aceptar</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const actualizarBtn = document.getElementById('actualizarBtn');
            const fechaInput = document.getElementById('fecha');
            const modal = document.getElementById('modal');
            const modalText = document.getElementById('modalText');

            actualizarBtn.addEventListener('click', function(event) {
                const fechaSeleccionada = new Date(fechaInput.value + 'T11:00:00');
                const ahora = new Date();

                if (ahora >= fechaSeleccionada) {
                    event.preventDefault(); // Prevenir el envío del formulario
                    modalText.innerText = `Este pedido se podía actualizar hasta el ${fechaSeleccionada.toLocaleDateString('es-ES')} a las 11hs.`;
                    modal.style.display = 'block';
                }
            });
        });

        function redirigirAlDashboard() {
            window.location.href = 'dashboard_cuyo_placa.php';
        }
    </script>
</body>
</html>
