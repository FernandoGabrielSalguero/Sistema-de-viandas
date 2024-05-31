<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../index.php');
    exit;
}

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    // Insertar el nuevo usuario en la tabla de usuarios
    $query = "INSERT INTO usuarios (nombre, apellido, usuario, contrasena, telefono, correo, rol) VALUES ('$nombre', '$apellido', '$usuario', '$contrasena', '$telefono', '$correo', '$rol')";
    if (mysqli_query($conn, $query)) {
        $usuario_id = mysqli_insert_id($conn);

        // Insertar los hijos del usuario en la tabla de hijos
        if (isset($_POST['hijo_nombre']) && isset($_POST['hijo_curso'])) {
            $hijo_nombres = $_POST['hijo_nombre'];
            $hijo_cursos = $_POST['hijo_curso'];

            for ($i = 0; $i < count($hijo_nombres); $i++) {
                $hijo_nombre = $hijo_nombres[$i];
                $hijo_curso = $hijo_cursos[$i];

                if (!empty($hijo_nombre) && !empty($hijo_curso)) {
                    $query_hijo = "INSERT INTO hijos (usuario_id, nombre, curso) VALUES ('$usuario_id', '$hijo_nombre', '$hijo_curso')";
                    mysqli_query($conn, $query_hijo);
                }
            }
        }

        echo "Usuario creado con éxito.";
    } else {
        echo "Error al crear el usuario: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Administrador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Crear Usuario</h1>
        <a href="dashboard_admin.php">Volver al Dashboard</a>
    </div>
</body>
</html>