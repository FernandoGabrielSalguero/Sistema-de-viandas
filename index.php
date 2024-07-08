<?php
session_start();

// Comprobar si el usuario está autenticado
if (isset($_SESSION['user_id'])) {
    // Si el usuario está autenticado, redirige al dashboard o a la página principal
    header('Location: src/views/dashboard.php'); // Asegúrate de ajustar la ruta según la estructura de tus archivos
    exit();
} else {
    // Si el usuario no está autenticado, redirige al login
    header('Location: src/views/login.php'); // Asegúrate de que la ruta esté correctamente establecida
    exit();
}
