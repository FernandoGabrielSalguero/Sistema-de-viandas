<?php
session_start();
include 'includes/db_connect.php';

if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    $_SESSION['usuario'] = $user['usuario'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['usuario_id'] = $user['usuario_id']; // Asegúrate de que 'usuario_id' sea el nombre correcto del campo en tu base de datos

    if ($user['rol'] == 'Administrador') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit;
} else {
    echo "Usuario o contraseña incorrectos.";
}