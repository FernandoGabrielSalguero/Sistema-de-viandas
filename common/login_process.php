<?php
include 'common/db_connect.php';
session_start();

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->execute([$username, md5($password)]);
$user = $stmt->fetch();

if ($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    
    switch ($user['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'kitchen':
            header("Location: kitchen/dashboard.php");
            break;
        case 'parent':
            header("Location: parents/dashboard.php");
            break;
        case 'school':
            header("Location: school/dashboard.php");
            break;
        default:
            header("Location: login.php");
    }
} else {
    echo "Invalid username or password";
}