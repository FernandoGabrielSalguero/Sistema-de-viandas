<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario AND contrasena = :contrasena");
    $stmt->execute(['usuario' => $usuario, 'contrasena' => $contrasena]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['rol'] = $user['rol'];

        switch ($user['rol']) {
            case 'Administrador':
                header('Location: /admin/dashboard.php');
                break;
            case 'Usuario':
                header('Location: /user/dashboard.php');
                break;
            case 'Cocina':
                header('Location: /kitchen/dashboard.php');
                break;
        }
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="login-container">
    <form action="login.php" method="POST">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required>
        <label for="contrasena">Contraseña</label>
        <input type="password" id="contrasena" name="contrasena" required>
        <button type="submit">Login</button>
        <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
