<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: /softeng2/Admin/admin_loginxRegister.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "pc_tracking_system");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch totals
$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;
$total_faculty = $conn->query("SELECT COUNT(*) AS total FROM faculty")->fetch_assoc()['total'] ?? 0;
$total_rooms = $conn->query("SELECT COUNT(*) AS total FROM rooms")->fetch_assoc()['total'] ?? 0;
$total_sections = $conn->query("SELECT COUNT(*) AS total FROM sections")->fetch_assoc()['total'] ?? 0;

// Fetch recent students
$recent_students = $conn->query("SELECT s.student_name, sec.section_name, sec.year_level, c.course_name 
                                 FROM students s
                                 JOIN sections sec ON s.section_id = sec.section_id
                                 JOIN courses c ON sec.course_id = c.course_id
                                 ORDER BY s.student_id DESC LIMIT 5");
$recent_students = $recent_students ? $recent_students->fetch_all(MYSQLI_ASSOC) : [];

// Fetch recent faculty
$recent_faculty = $conn->query("SELECT faculty_name, department 
                                FROM faculty 
                                ORDER BY faculty_id DESC LIMIT 2");
$recent_faculty = $recent_faculty ? $recent_faculty->fetch_all(MYSQLI_ASSOC) : [];

// Fetch recent rooms
$recent_rooms = $conn->query("SELECT room_name, capacity 
                              FROM rooms 
                              ORDER BY room_id DESC LIMIT 2");
$recent_rooms = $recent_rooms ? $recent_rooms->fetch_all(MYSQLI_ASSOC) : [];

// Fetch recent sections
$recent_sections = $conn->query("SELECT section_name, year_level, course_name 
                                 FROM sections 
                                 JOIN courses ON sections.course_id = courses.course_id 
                                 ORDER BY section_id DESC LIMIT 2");
$recent_sections = $recent_sections ? $recent_sections->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .recent-list {
            list-style: none;
            padding: 0;
        }
        .recent-list li {
            margin-bottom: 10px;
        }
        .recent-list li i {
            margin-right: 10px;
        }
        .view-all {
            text-decoration: none;
            font-weight: bold;
            color: #007bff;
        }
        .view-all:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-center fw-bold">CCS Admin</h4>
        <div class="text-center mb-3">
            <img src="path/to/admin-avatar.png" alt="Admin Avatar" class="rounded-circle" width="80">
            <p class="mt-2 mb-0 fw-bold">John Administrator</p>
            <small class="text-muted">System Administrator</small>
        </div>
        <hr>
        <p class="text-uppercase text-muted px-3">Main</p>
        <a href="admin_dashboard.php"><i class="fas fa-th-large me-2"></i>Dashboard</a>
        <p class="text-uppercase text-muted px-3 mt-3">Management</p>
        <a href="admin_students.php"><i class="fas fa-user-graduate me-2"></i>Students</a>
        <a href="admin_faculty.php"><i class="fas fa-chalkboard-teacher me-2"></i>Faculty</a>
        <a href="admin_rooms.php"><i class="fas fa-door-open me-2"></i>Rooms</a>
        <a href="admin_sections.php"><i class="fas fa-users me-2"></i>Sections</a>
        <p class="text-uppercase text-muted px-3 mt-3">Settings</p>
        <a href="admin_settings.php"><i class="fas fa-cog me-2"></i>Settings</a>
        <a href="admin_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container py-5">
            <h2 class="mb-4">Dashboard</h2>
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="card p-3">
                        <i class="fas fa-user-graduate fa-2x mb-2"></i>
                        <h5>Total Students</h5>
                        <p class="mb-0"><?php echo $total_students; ?> active</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                        <h5>Faculty Members</h5>
                        <p class="mb-0"><?php echo $total_faculty; ?> active</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <i class="fas fa-door-open fa-2x mb-2"></i>
                        <h5>Total Rooms</h5>
                        <p class="mb-0"><?php echo $total_rooms; ?> available</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h5>Active Sections</h5>
                        <p class="mb-0"><?php echo $total_sections; ?> active</p>
                    </div>
                </div>
            </div>

            <div class="tabs mt-5">
                <ul class="nav nav-tabs" id="recentTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab" aria-controls="students" aria-selected="true">Students</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="faculty-tab" data-bs-toggle="tab" data-bs-target="#faculty" type="button" role="tab" aria-controls="faculty" aria-selected="false">Faculty</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="rooms-tab" data-bs-toggle="tab" data-bs-target="#rooms" type="button" role="tab" aria-controls="rooms" aria-selected="false">Rooms</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="sections-tab" data-bs-toggle="tab" data-bs-target="#sections" type="button" role="tab" aria-controls="sections" aria-selected="false">Sections</button>
                    </li>
                </ul>
                <div class="tab-content mt-4" id="recentTabsContent">
                    <div class="tab-pane fade show active" id="students" role="tabpanel" aria-labelledby="students-tab">
                        <h5>Recent Students</h5>
                        <ul class="recent-list">
                            <?php foreach ($recent_students as $student): ?>
                                <li>
                                    <i class="fas fa-user-graduate"></i>
                                    <?php echo $student['student_name']; ?> - 
                                    <?php echo $student['course_name']; ?>, 
                                    <?php echo $student['year_level']; ?> - 
                                    <?php echo $student['section_name']; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="faculty" role="tabpanel" aria-labelledby="faculty-tab">
                        <h5>Recent Faculty</h5>
                        <ul class="recent-list">
                            <?php foreach ($recent_faculty as $faculty): ?>
                                <li>
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    <?php echo $faculty['faculty_name']; ?> - 
                                    <?php echo $faculty['department']; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="rooms" role="tabpanel" aria-labelledby="rooms-tab">
                        <h5>Recent Rooms</h5>
                        <ul class="recent-list">
                            <?php foreach ($recent_rooms as $room): ?>
                                <li>
                                    <i class="fas fa-door-open"></i>
                                    <?php echo $room['room_name']; ?> - 
                                    Capacity: <?php echo $room['capacity']; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="sections" role="tabpanel" aria-labelledby="sections-tab">
                        <h5>Recent Sections</h5>
                        <ul class="recent-list">
                            <?php foreach ($recent_sections as $section): ?>
                                <li>
                                    <i class="fas fa-users"></i>
                                    <?php echo $section['section_name']; ?> - 
                                    <?php echo $section['course_name']; ?>, 
                                    Year: <?php echo $section['year_level']; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>    
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>