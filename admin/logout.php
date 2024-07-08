<?php
session_start();
session_unset();
session_destroy();
header("Location: /viandas/index.php"); // Redirige al login
exit();
