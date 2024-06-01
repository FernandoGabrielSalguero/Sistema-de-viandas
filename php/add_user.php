<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    $sql = "INSERT INTO usuarios (nombre, apellido, usuario, contraseña, telefono, correo, rol) 
            VALUES ('$nombre', '$apellido', '$usuario', '$contraseña', '$telefono', '$correo', '$rol')";
    
    if ($conn->query($sql) === TRUE) {
        $usuario_id = $conn->insert_id;

        if ($rol == 'Usuario' && isset($_POST['hijos'])) {
            foreach ($_POST['hijos'] as $hijo) {
                $hijo_nombre = $hijo['nombre'];
                $hijo_apellido = $hijo['apellido'];
                $hijo_curso_id = $hijo['curso_id'];
                $hijo_colegio_id = $hijo['colegio_id'];
                $hijo_notas = $hijo['notas'];

                $sql = "INSERT INTO hijos (nombre, apellido, curso_id, colegio_id, notas, usuario_id) 
                        VALUES ('$hijo_nombre', '$hijo_apellido', '$hijo_curso_id', '$hijo_colegio_id', '$hijo_notas', $usuario_id)";
                $conn->query($sql);
            }
        }

        header("Location: ../views/manage_users.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
