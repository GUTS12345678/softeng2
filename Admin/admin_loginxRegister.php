<?php
session_start();
$conn = new mysqli("localhost", "root", "", "pc_tracking_system");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($conn->real_escape_string($_POST['password'])); // Hash the password

    $query = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $_SESSION['admin'] = $result->fetch_assoc();
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($conn->real_escape_string($_POST['password'])); // Hash the password
    $confirm_password = md5($conn->real_escape_string($_POST['confirm_password']));
    $full_name = $conn->real_escape_string($_POST['full_name']);

    if ($password !== $confirm_password) {
        $register_error = "Passwords do not match.";
    } else {
        // Check if username already exists
        $check_query = "SELECT * FROM admin WHERE username = '$username'";
        $check_result = $conn->query($check_query);

        if ($check_result->num_rows > 0) {
            $register_error = "Username already exists.";
        } else {
            // Insert new admin
            $query = "INSERT INTO admin (username, password, full_name) VALUES ('$username', '$password', '$full_name')";
            if ($conn->query($query)) {
                $register_success = "Registration successful. You can now log in.";
            } else {
                $register_error = "Error registering user: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Information System - Admin Login & Register</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .tab-button {
            cursor: pointer;
            padding: 10px 20px;
            border: none;
            background-color: transparent;
            font-size: 16px;
            font-weight: 600;
        }
        .tab-button.active {
            border-bottom: 2px solid #1d4ed8;
            color: #1d4ed8;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <button class="toggle-btn" id="toggleSidebar">☰</button>
    <div class="sidebar" id="sidebar">
        <h4 class="text-center fw-bold">CCS Information System</h4>
        <a href="../index.php"><i class="fas fa-home me-2"></i>Home</a>
        <a href="/softeng2/Admin/admin_loginxRegister.php" class="active"><i class="fas fa-user-shield me-2"></i>Login as Admin</a>
        <a href="../room_login.php"><i class="fas fa-door-open me-2"></i>Login to Room</a>
        <a href="../pc_tracking.php"><i class="fas fa-desktop me-2"></i>PC Tracking</a>
        <a href="../submit_documents.php"><i class="fas fa-file-upload me-2"></i>Submit Documents</a>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Main Content -->
    <div class="content" id="content">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-center mb-4">
                                <button class="tab-button active" id="loginTab">Login</button>
                                <button class="tab-button" id="registerTab">Register</button>
                            </div>
                            <!-- Login Form -->
                            <div id="loginForm">
                                <h3 class="text-center mb-4">Admin Login</h3>
                                <?php if (isset($error)) : ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" name="username" id="username" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" name="password" id="password" class="form-control" required>
                                    </div>
                                    <button type="submit" name="login" class="btn btn-danger w-100">Login</button>
                                </form>
                            </div>
                            <!-- Register Form -->
                            <div id="registerForm" class="d-none">
                                <h3 class="text-center mb-4">Admin Register</h3>
                                <?php if (isset($register_error)) : ?>
                                    <div class="alert alert-danger"><?php echo $register_error; ?></div>
                                <?php endif; ?>
                                <?php if (isset($register_success)) : ?>
                                    <div class="alert alert-success"><?php echo $register_success; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">Full Name</label>
                                        <input type="text" name="full_name" id="full_name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" name="username" id="username" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" name="password" id="password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                    </div>
                                    <button type="submit" name="register" class="btn btn-success w-100">Register</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const loginTab = document.getElementById('loginTab');
        const registerTab = document.getElementById('registerTab');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');

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

        loginTab.addEventListener('click', () => {
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            loginForm.classList.remove('d-none');
            registerForm.classList.add('d-none');
        });

        registerTab.addEventListener('click', () => {
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            registerForm.classList.remove('d-none');
            loginForm.classList.add('d-none');
        });
    </script>
</body>
</html>