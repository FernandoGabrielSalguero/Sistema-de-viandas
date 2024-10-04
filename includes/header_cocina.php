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

// Consultar las notificaciones pendientes
$consulta_notificaciones = $pdo->prepare("SELECT COUNT(*) as pendientes FROM notificaciones_cocina WHERE estado = 'pendiente'");
$consulta_notificaciones->execute();
$notificaciones = $consulta_notificaciones->fetch(PDO::FETCH_ASSOC);
$pendientes = $notificaciones['pendientes'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Cocina</title>
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

        /* Estilos para el badge de notificaciones */
        .badge {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border-radius: 50%;
            font-size: 14px;
            position: absolute;
            top: -10px;
            right: -10px;
        }

        /* Estilos del dropdown */
        .notificaciones-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 300px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            display: none;
            padding: 10px;
            z-index: 1000;
        }

        .notificacion {
            margin-bottom: 10px;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .notificacion button {
            margin-top: 5px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
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
                <a href="#" id="notificaciones-btn">Notificaciones
                    <span class="badge" id="notificaciones-badge"><?php echo $pendientes; ?></span>
                </a>
                <div class="notificaciones-dropdown" id="notificaciones-dropdown">
                    Cargando notificaciones...
                </div>
            </li>
            <li><a href="logout.php">Salir</a></li>
        </ul>
    </nav>

    <!-- Sonido de notificación -->
    <audio id="alert-sound" src="../css/Notificacion.mp3"></audio>

    <script>
        // Función para actualizar las notificaciones
        function actualizarNotificaciones() {
            fetch('obtener_notificaciones.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al cargar notificaciones.');
                    }
                    return response.json(); // Convertir la respuesta a JSON
                })
                .then(data => {
                    console.log(data); // Depuración: Ver qué datos estamos recibiendo
                    const badge = document.getElementById('notificaciones-badge');
                    const dropdown = document.getElementById('notificaciones-dropdown');
                    const alertSound = document.getElementById('alert-sound');

                    // Actualizar el badge con el número de notificaciones pendientes
                    badge.innerText = data.length;

                    // Limpiar el contenido anterior del dropdown
                    dropdown.innerHTML = '';

                    // Si no hay notificaciones, mostrar el mensaje de "No hay cambios"
                    if (data.length === 0) {
                        dropdown.innerHTML = '<p>No hay cambios por el momento, próxima actualización en 15 minutos.</p>';
                    } else {
                        // Reproducir sonido si hay nuevas notificaciones
                        alertSound.play();

                        // Recorrer y mostrar las notificaciones pendientes
                        data.forEach(notificacion => {
                            const notificacionElement = document.createElement('div');
                            notificacionElement.classList.add('notificacion');

                            notificacionElement.innerHTML = `
                            <p><strong>Tipo:</strong> ${notificacion.tipo}</p>
                            <p><strong>Nombre:</strong> ${notificacion.Nombre}</p>
                            <p><strong>Descripción:</strong> ${notificacion.descripcion}</p>
                            <button onclick="marcarComoVisto(${notificacion.id})">Visto</button>
                        `;

                            dropdown.appendChild(notificacionElement);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error al cargar notificaciones:', error);
                    document.getElementById('notificaciones-dropdown').innerHTML = '<p>Error al cargar las notificaciones.</p>';
                });
        }

        // Función para marcar una notificación como vista
        function marcarComoVisto(id) {
            fetch(`marcar_visto.php?id=${id}`)
                .then(response => response.text())
                .then(result => {
                    if (result === 'ok') {
                        actualizarNotificaciones(); // Volver a actualizar después de marcar como visto
                    }
                });
        }

        // Mostrar el dropdown al hacer clic en "Notificaciones"
        document.getElementById('notificaciones-btn').addEventListener('click', function() {
            const dropdown = document.getElementById('notificaciones-dropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Actualizar las notificaciones automáticamente cada 15 minutos (900000 ms)
        setInterval(actualizarNotificaciones, 900000);

        // Llamar a la función al cargar la página
        actualizarNotificaciones();
    </script>


</body>

</html>