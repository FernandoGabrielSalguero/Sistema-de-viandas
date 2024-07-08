<?php
include 'includes/db.php';

$nombre = 'Juan Perez';
$usuario = 'yoyi';
$contrasena = password_hash('yoyi', PASSWORD_BCRYPT);
$telefono = '123456789';
$correo = 'juan@example.com';
$rol = 'papas';

$stmt = $pdo->prepare("INSERT INTO Usuarios (Nombre, Usuario, Contrasena, Telefono, Correo, Rol) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$nombre, $usuario, $contrasena, $telefono, $correo, $rol]);
echo "Usuario creado con éxito";
