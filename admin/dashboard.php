<?php
include '../common/header.php';

// Verificar si el usuario tiene el rol de administrador
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<div class="container">
    <h1>Admin Dashboard</h1>
    <p>Welcome, Admin!</p>
    <nav>
        <ul>
            <li><button onclick="window.location.href='schools.php'">Manage Schools</button></li>
            <li><button onclick="window.location.href='courses.php'">Manage Courses</button></li>
            <li><button onclick="window.location.href='parents.php'">Manage Parents</button></li>
            <li><button onclick="window.location.href='children.php'">Manage Children</button></li>
            <li><button onclick="window.location.href='verify_recharge.php'">Verificar Recargas</button></li>
        </ul>
    </nav>
</div>
</body>
</html>
