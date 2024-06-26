<?php
session_start();
session_destroy();
echo "<script>alert('Muchas gracias por visitarnos');</script>";
header('Location: login.php');
exit();