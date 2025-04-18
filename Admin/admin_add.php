<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "pc_tracking_system");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($conn->real_escape_string($_POST['password']));
    $full_name = $conn->real_escape_string($_POST['full_name']);

    $query = "INSERT INTO admin (username, password, full_name) VALUES ('$username', '$password', '$full_name')";
    $conn->query($query);

    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #dc3545;
            color: white;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
            transform: translateX(-250px);
            transition: transform 0.3s ease, background-color 0.3s ease;
            z-index: 1000;
        }
        .sidebar.open {
            transform: translateX(0);
            background-color: #b02a37;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #a02832;
        }
        .toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            z-index: 1100;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: left 0.3s ease;
        }
        .toggle-btn.shifted {
            left: 270px;
        }
        .content {
            margin-left: 0;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 900;
            display: none;
            transition: opacity 0.3s ease;
        }
        .overlay.visible {
            display: block;
            opacity: 1;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar -->
    <button class="toggle-btn" id="toggleSidebar">☰</button>
    <div class="sidebar" id="sidebar">
        <h4 class="text-center fw-bold">Admin Dashboard</h4>
        <a href="../index.php"><i class="fas fa-home me-2"></i>Home</a>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_students.php">Manage Students</a>
        <a href="admin_add.php">Manage Admins</a>
        <a href="admin_logout.php">Logout</a>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Main Content -->
    <div class="content">
        <div class="container py-5">
            <h2 class="text-center mb-4">Add Admin</h2>
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" name="full_name" id="full_name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Add Admin</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('visible');
            toggleSidebar.classList.toggle('shifted');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('visible');
            toggleSidebar.classList.remove('shifted');
        });
    </script>
</body>
</html>