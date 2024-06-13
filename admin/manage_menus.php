<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: /login.php');
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Código para agregar, editar o eliminar menús
}

$menus = $pdo->query("SELECT * FROM menus")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="manage-menus">
    <h1>Gestionar Menús</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($menus as $menu) { ?>
            <tr>
                <td><?php echo $menu['id']; ?></td>
                <td><?php echo $menu['nombre']; ?></td>
                <td><?php echo $menu['precio']; ?></td>
                <td><?php echo $menu['fecha']; ?></td>
                <td>
                    <a href="edit_menu.php?id=<?php echo $menu['id']; ?>">Editar</a>
                    <a href="delete_menu.php?id=<?php echo $menu['id']; ?>">Eliminar</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php include '../includes/footer.php'; ?>
