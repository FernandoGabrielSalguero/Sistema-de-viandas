<?php
// Habilitar la muestra de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';
include 'functions.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cocina') {
    header("Location: ../login.php");
    exit();
}

// Inicializar la variable $pendientes por defecto
$pendientes = 0;

try {
    // Consultar las notificaciones pendientes
    $consulta_notificaciones = $pdo->prepare("SELECT COUNT(*) as pendientes FROM notificaciones_cocina WHERE estado = 'pendiente'");
    $consulta_notificaciones->execute();
    $notificaciones = $consulta_notificaciones->fetch(PDO::FETCH_ASSOC);
    if ($notificaciones && isset($notificaciones['pendientes'])) {
        $pendientes = $notificaciones['pendientes']; // Asignar el valor real si la consulta es exitosa
    }
} catch (PDOException $e) {
    // Manejo de error: puedes registrar el error o mostrar un mensaje en el log
    error_log("Error al consultar las notificaciones pendientes: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            background-color: #007bff;
            padding: 20px;
        }

        nav ul li {
            position: relative;
        }

        nav ul li a {
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            color: #fff;
            border-radius: 5px;
            margin: 5px;
            text-align: center;
            font-weight: bold;
        }

        /* Estilo del botón de notificaciones */
        .badge {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border-radius: 50%;
            font-size: 0.8em;
            position: absolute;
            top: 0;
            right: 0;
            transform: translate(50%, -50%);
        }

        /* Estilos para el dropdown */
        .dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1px solid #ddd;
            width: 300px;
            display: none;
            flex-direction: column;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .dropdown.active {
            display: flex;
        }

        .dropdown-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item .visto-btn {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 5px;
        }

        .dropdown-item p {
            margin: 0;
            font-size: 0.9em;
        }

        .no-notificaciones {
            padding: 10px;
            text-align: center;
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>

<body>
    <nav>
        <ul>
            <li><a href="pedidos_colegios.php">Colegios</a></li>
            <li><a href="pedidos_cuyo.php">Cuyo Placas</a></li>
            <li><a href="pedidos_hyt_cocina.php">H&T</a></li>
            <li>
                <a href="#" onclick="mostrarNotificaciones()">Notificaciones
                    <span class="badge" id="notificaciones-count"><?php echo $pendientes; ?></span>
                </a>
                <div class="dropdown" id="notificaciones-dropdown">
                    <!-- Aquí se cargarán las notificaciones -->
                    <div class="no-notificaciones">Cargando notificaciones...</div>
                </div>
            </li>
            <li><a href="logout.php">Salir</a></li>
        </ul>
    </nav>

    <script>
        // Función para mostrar el desplegable de notificaciones
        function mostrarNotificaciones() {
            const dropdown = document.getElementById('notificaciones-dropdown');
            dropdown.classList.toggle('active');

            if (dropdown.classList.contains('active')) {
                cargarNotificaciones(); // Cargar notificaciones si está activo
            }
        }

        // Función para cargar las notificaciones
        function cargarNotificaciones() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'obtener_notificaciones.php?action=list', true);
            xhr.onload = function () {
                if (this.status === 200) {
                    const dropdown = document.getElementById('notificaciones-dropdown');
                    dropdown.innerHTML = this.responseText;
                }
            }
            xhr.send();
        }

        // Función para marcar notificación como "vista"
        function marcarComoVisto(id) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'obtener_notificaciones.php?action=mark_seen', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (this.status === 200) {
                    cargarNotificaciones(); // Recargar notificaciones
                    document.getElementById('notificaciones-count').innerText = this.responseText;
                }
            }
            xhr.send('id=' + id);
        }
    </script>
</body>

</html>
