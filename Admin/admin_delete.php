<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "pc_tracking_system");

$id = intval($_GET['id']);
$query = "DELETE FROM admin WHERE admin_id = $id";
$conn->query($query);

header("Location: admin_dashboard.php");
exit;
?>