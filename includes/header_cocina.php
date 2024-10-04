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

// Inicialmente se cargarán las notificaciones pendientes desde el servidor
$consulta_notificaciones = $pdo->prepare("SELECT COUNT(*) as pendientes FROM notificaciones_cocina WHERE estado = 'pendiente'");
$consulta_notificaciones->execute();
$notificaciones = $consulta_notificaciones->fetch(PDO::FETCH_ASSOC);
$pendientes = $notificaciones['pendientes'];
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
    </style>
</head>

<body>
    <nav>
        <ul>
            <li><a href="pedidos_colegios.php">Colegios</a></li>
            <li><a href="pedidos_cuyo.php">Cuyo Placas</a></li>
            <li><a href="pedidos_hyt_cocina.php">H&T</a></li>
            <li>
                <a href="notificaciones_cocina.php">Notificaciones
                    <span class="badge" id="notificaciones-count"><?php echo $pendientes; ?></span>
                </a>
            </li>
            <li><a href="logout.php">Salir</a></li>
        </ul>
    </nav>

    <script>
        // Función para hacer la petición AJAX
        function actualizarNotificaciones() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'obtener_notificaciones.php', true); // Archivo PHP que devolverá el conteo de notificaciones
            xhr.onload = function () {
                if (this.status === 200) {
                    document.getElementById('notificaciones-count').innerText = this.responseText;
                }
            }
            xhr.send();
        }

        // Actualizar notificaciones cada 5 segundos
        setInterval(actualizarNotificaciones, 5000);
    </script>
</body>

</html>
