<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: /login.php');
    exit;
}

include '../includes/db.php';
include '../includes/header.php';
?>
<div class="admin-dashboard">
    <h1>Bienvenido, Administrador</h1>
    <nav>
        <ul>
            <li><a href="manage_users.php">Gestionar Usuarios</a></li>
            <li><a href="manage_menus.php">Gestionar Menús</a></li>
            <li><a href="manage_recharges.php">Gestionar Recargas de Saldo</a></li>
        </ul>
    </nav>
</div>
<?php include '../includes/footer.php'; ?>
