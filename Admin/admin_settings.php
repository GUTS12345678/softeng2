<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: /softeng2/Admin/admin_loginxRegister.php");
    exit;
}

// Include the header and database connection
require_once '../admin-settings-management/src/common/header.php';
require_once '../admin-settings-management/src/common/database.php';

// Determine which settings page to load
$page = isset($_GET['page']) ? $_GET['page'] : 'account-settings';

switch ($page) {
    case 'account-settings':
        require_once '../admin-settings-management/src/account-settings/index.php';
        break;
    case 'user-management':
        require_once '../admin-settings-management/src/user-management/index.php';
        break;
    case 'system-configuration':
        require_once '../admin-settings-management/src/system-configuration/index.php';
        break;
    case 'notification-settings':
        require_once '../admin-settings-management/src/notification-settings/index.php';
        break;
    case 'backup-restore':
        require_once '../admin-settings-management/src/backup-restore/index.php';
        break;
    case 'security-settings':
        require_once '../admin-settings-management/src/security-settings/index.php';
        break;
    default:
        echo "<h1>Welcome to Admin Settings</h1>";
        echo "<p>Please select a section from the navigation menu.</p>";
        break;
}

// Include the footer
require_once '../admin-settings-management/src/common/footer.php';
?>

<link rel="stylesheet" href="../public/css/styles.css">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
}

nav {
    background-color: #343a40;
    padding: 10px;
}

nav ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
}

nav ul li {
    margin: 0 15px;
}

nav ul li a {
    color: white;
    text-decoration: none;
    font-weight: bold;
}

nav ul li a:hover {
    text-decoration: underline;
}

.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

p {
    text-align: center;
    color: #6c757d;
}
</style>