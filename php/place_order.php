<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['userid'];
    $hijo_id = $_POST['hijo_id'];
    $menus = $_POST['menu_id'];
    
    // Iniciar la transacción
    $conn->begin_transaction();

    try {
        foreach ($menus as $fecha => $menu_id) {
            $sql = "INSERT INTO pedidos (usuario_id, hijo_id, menu_id, estado) 
                    VALUES ('$usuario_id', '$hijo_id', '$menu_id', 'En espera de aprobación')";
            $conn->query($sql);
        }

        // Confirmar la transacción
        $conn->commit();
        header("Location: ../views/user_dashboard.php");
    } catch (Exception $e) {
        // Revertir la transacción
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}