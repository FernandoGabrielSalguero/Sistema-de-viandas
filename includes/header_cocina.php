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

// Consultar el número de notificaciones pendientes
$consulta_notificaciones = $pdo->prepare("SELECT COUNT(*) as pendientes FROM notificaciones_cocina WHERE estado = 'pendiente'");
$consulta_notificaciones->execute();
$notificaciones = $consulta_notificaciones->fetch(PDO::FETCH_ASSOC);
$pendientes = $notificaciones['pendientes'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

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

        /* Estilo para el icono de notificaciones y el badge */
        .notificaciones-boton {
            position: relative;
        }

        .notificaciones-boton .badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
        }

        /* Estilo del dropdown */
        .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            display: none;
            z-index: 1000;
            border-radius: 5px;
            padding: 10px;
        }

        .dropdown p {
            margin: 0;
            padding: 5px;
        }

        .dropdown .visto {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            margin-top: 5px;
            cursor: pointer;
            border-radius: 5px;
        }

        .dropdown .visto:hover {
            background-color: #0056b3;
        }

        .notificacion {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .notificacion:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <nav>
        <ul>
            <li><a href="pedidos_colegios.php">Colegios</a></li>
            <li><a href="pedidos_cuyo.php">Cuyo Placas</a></li>
            <li><a href="pedidos_hyt_cocina.php">H&T</a></li>
            <li class="notificaciones-boton">
                <a href="#" id="notificaciones">Notificaciones
                    <span class="badge"><?php echo $pendientes; ?></span>
                </a>
                <div class="dropdown" id="dropdown-notificaciones">
                    Cargando notificaciones...
                </div>
            </li>
            <li><a href="logout.php">Salir</a></li>
        </ul>
    </nav>

    <script>
        document.getElementById('notificaciones').addEventListener('click', function(event) {
            event.preventDefault();
            var dropdown = document.getElementById('dropdown-notificaciones');

            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                dropdown.style.display = 'block';

                fetch('../includes/obtener_notificaciones.php') 
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            dropdown.innerHTML = "<p>No hay cambios por el momento, próxima actualización en 15 minutos</p>";
                        } else {
                            dropdown.innerHTML = ''; 
                            data.forEach(notificacion => {
                                let notificacionHTML = `
                    <div class="notificacion">
                        <p><strong>Tipo:</strong> ${notificacion.tipo}</p>
                        <p><strong>Nombre:</strong> ${notificacion.Nombre}</p>
                        <p><strong>Descripción:</strong> ${notificacion.descripcion}</p>
                        <button class="visto" data-id="${notificacion.id}">Visto</button>
                    </div>`;
                                dropdown.innerHTML += notificacionHTML;
                            });

                            // Agregar manejadores a los botones "Visto"
                            document.querySelectorAll('.visto').forEach(boton => {
                                boton.addEventListener('click', function() {
                                    var notificacionId = this.getAttribute('data-id');
                                    fetch('../includes/marcar_visto.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            id: notificacionId
                                        })
                                    }).then(response => {
                                        if (response.ok) {
                                            this.closest('.notificacion').remove();
                                        }
                                    });
                                });
                            });
                        }
                    })
                    .catch(error => {
                        dropdown.innerHTML = "<p>Error al cargar las notificaciones.</p>";
                    });
            } else {
                dropdown.style.display = 'none';
            }
        });
    </script>
</body>

</html>