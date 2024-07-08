<?php
include 'includes/db.php';

$nombre = 'Juan Perez';
$usuario = 'juan';
$contrasena = password_hash('mi_password_seguro', PASSWORD_BCRYPT);
$telefono = '123456789';
$correo = 'juan@example.com';
$rol = 'administrador';

$stmt = $pdo->prepare("INSERT INTO Usuarios (Nombre, Usuario, Contrasena, Telefono, Correo, Rol) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$nombre, $usuario, $contrasena, $telefono, $correo, $rol]);
echo "Usuario creado con éxito";
