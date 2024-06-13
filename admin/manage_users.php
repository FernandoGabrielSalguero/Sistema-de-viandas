<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: /login.php');
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Código para agregar, editar o eliminar usuarios
}

$usuarios = $pdo->query("SELECT * FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="manage-users">
    <h1>Gestionar Usuarios</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Saldo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario) { ?>
            <tr>
                <td><?php echo $usuario['id']; ?></td>
                <td><?php echo $usuario['nombre']; ?></td>
                <td><?php echo $usuario['apellido']; ?></td>
                <td><?php echo $usuario['usuario']; ?></td>
                <td><?php echo $usuario['rol']; ?></td>
                <td><?php echo $usuario['saldo']; ?></td>
                <td>
                    <a href="edit_user.php?id=<?php echo $usuario['id']; ?>">Editar</a>
                    <a href="delete_user.php?id=<?php echo $usuario['id']; ?>">Eliminar</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>
