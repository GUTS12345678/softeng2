<?php
session_start();
session_destroy();
header("Location: /softeng2/Admin/admin_loginxRegister.php");  
exit;
?>