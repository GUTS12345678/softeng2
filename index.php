<?php
require_once 'db_connect.php';

// Fetch totals from the database
$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$total_faculty = $conn->query("SELECT COUNT(*) AS total FROM faculty")->fetch_assoc()['total'];
$total_rooms = $conn->query("SELECT COUNT(*) AS total FROM rooms")->fetch_assoc()['total'];
$total_sections = $conn->query("SELECT COUNT(*) AS total FROM sections")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Information System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css">
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
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2); /* Added shadow for better separation */
            transform: translateX(-250px);
            transition: transform 0.3s ease, background-color 0.3s ease;
            z-index: 1000;
        }
        .sidebar.open {
            transform: translateX(0);
            background-color: #b02a37; /* Highlight color when opened */
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
        .centered-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px; /* Added margin to separate from the sidebar */
        }
        .container-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .container-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .svg-white {
            filter: invert(1);
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
        <h4 class="text-center fw-bold">CCS Information System</h4>
        <a href="index.php"><i class="fas fa-home me-2"></i>Home</a>
        <a href="/softeng2/Admin/admin_loginxRegister.php">Login as Admin</a> <!-- Updated link -->
        <a href="room_login.php">Login to Room</a>
        <a href="pc_tracking.php">PC Tracking</a>
        <a href="submit_documents.php">Submit Documents</a>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Main Content -->
    <div class="content" id="content">
        <div class="container py-5">
            <div class="centered-content">
                <div class="card bg-danger text-white shadow-lg container-hover" style="width: 18rem;">
                    <div class="card-body text-center">
                        <i class="fas fa-user-graduate fa-3x mb-3"></i>
                        <h5 class="card-title">Students</h5>
                        <a href="students.php" class="btn btn-light text-danger">View Students</a>
                    </div>
                </div>
                <div class="card bg-danger text-white shadow-lg container-hover" style="width: 18rem;">
                    <div class="card-body text-center">
                        <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                        <h5 class="card-title">Faculty</h5>
                        <a href="faculty.php" class="btn btn-light text-danger">View Faculty</a>
                    </div>
                </div>
                <div class="card bg-danger text-white shadow-lg container-hover" style="width: 18rem;">
                    <div class="card-body text-center">
                        <i class="fas fa-door-open fa-3x mb-3"></i>
                        <h5 class="card-title">Rooms</h5>
                        <a href="rooms.php" class="btn btn-light text-danger">View Rooms</a>
                    </div>
                </div>
                <div class="card bg-danger text-white shadow-lg container-hover" style="width: 18rem;">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5 class="card-title">Sections</h5>
                        <a href="section.php" class="btn btn-light text-danger">View Sections</a>
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
