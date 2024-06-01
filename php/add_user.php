<?php
// add_user.php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    // Iniciar la transacción
    $conn->begin_transaction();

    try {
        // Insertar usuario
        $sql = "INSERT INTO usuarios (nombre, apellido, usuario, contraseña, telefono, correo, rol) 
                VALUES ('$nombre', '$apellido', '$usuario', '$contraseña', '$telefono', '$correo', '$rol')";
        $conn->query($sql);
        $usuario_id = $conn->insert_id;

        // Insertar hijos si el rol es 'Usuario'
        if ($rol == 'Usuario' && isset($_POST['hijos'])) {
            foreach ($_POST['hijos'] as $hijo) {
                $hijo_nombre = $hijo['nombre'];
                $hijo_apellido = $hijo['apellido'];
                $hijo_curso = $hijo['curso'];
                $hijo_colegio = $hijo['colegio'];
                $hijo_notas = $hijo['notas'];

                $sql = "INSERT INTO hijos (nombre, apellido, curso, colegio, notas, usuario_id) 
                        VALUES ('$hijo_nombre', '$hijo_apellido', '$hijo_curso', '$hijo_colegio', '$hijo_notas', '$usuario_id')";
                $conn->query($sql);
            }
        }

        // Confirmar la transacción
        $conn->commit();
        header("Location: ../views/manage_users.php");
    } catch (Exception $e) {
        // Revertir la transacción
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
