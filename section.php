<?php
$conn = new mysqli("localhost", "root", "", "pc_tracking_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;

// Fetch sections for search results
$search_results = null;
if ($search) {
    $search_query = "SELECT section_id, section_name FROM sections 
                     WHERE section_name LIKE '%$search%' OR section_id LIKE '%$search%' 
                     ORDER BY section_name";
    $search_results = $conn->query($search_query);
}

// Fetch section details with course name
$section = null;
if ($section_id) {
    $section_query = "SELECT courses.course_name, sections.section_name, sections.year_level 
                      FROM sections 
                      JOIN courses ON sections.course_id = courses.course_id 
                      WHERE sections.section_id = $section_id";
    $section_result = $conn->query($section_query);
    $section = $section_result->fetch_assoc();
}

// Fetch schedule for the section
$schedule_result = null;
if ($section_id) {
    $schedule_query = "SELECT schedule.time, schedule.day, subjects.subject_name, faculty.faculty_name, schedule.subject_id, schedule.faculty_id 
                       FROM schedule 
                       JOIN subjects ON schedule.subject_id = subjects.subject_id 
                       JOIN faculty ON schedule.faculty_id = faculty.faculty_id 
                       WHERE schedule.section_id = $section_id";
    $schedule_result = $conn->query($schedule_query);
}

// Fetch students in the section
$students_result = null;
if ($section_id) {
    $students_query = "SELECT student_id, student_name FROM students WHERE section_id = $section_id";
    $students_result = $conn->query($students_query);
}
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
        <h4 class="text-center fw-bold">CCS Information System</h4>
        <a href="index.php"><i class="fas fa-home me-2"></i>Home</a>
        <a href="faculty.php">Faculty</a>
        <a href="rooms.php">Rooms</a>
        <a href="students.php">Students</a>
        <a href="section.php">Sections</a>
        <a href="submit_documents.php">Submit Documents</a>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Main Content -->
    <div class="content">
        <div class="container py-5">
            <h2 class="text-center mb-4">Section Profile</h2>

            <!-- Search Bar -->
            <form method="GET" class="d-flex mb-4">
                <input type="text" name="search" placeholder="Search by section name or ID..." 
                    class="form-control me-2" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-search me-2"></i>Search
                </button>
            </form>

            <!-- Search Results -->
            <?php if ($search && $search_results && $search_results->num_rows > 0) : ?>
                <div class="bg-white shadow rounded p-4 mb-6 animate__animated animate__fadeIn" id="search-results">
                    <h3 class="text-center mb-4">Search Results</h3>
                    <ul class="list-group">
                        <?php while ($row = $search_results->fetch_assoc()) : ?>
                            <li class="list-group-item">
                                <a href="section.php?section_id=<?php echo $row['section_id']; ?>" class="text-decoration-none text-primary">
                                    <?php echo htmlspecialchars($row['section_name']); ?> (ID: <?php echo $row['section_id']; ?>)
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            <?php elseif ($search) : ?>
                <p class="text-center text-muted">No sections found.</p>
            <?php endif; ?>

            <?php if ($section) : ?>
                <!-- Section Details -->
                <div class="bg-white shadow rounded p-4 mb-6 animate__animated animate__fadeIn" id="section-details">
                    <h3 class="text-center mb-4">Section Details</h3>
                    <p><strong>Course:</strong> <?php echo htmlspecialchars($section['course_name']); ?></p>
                    <p><strong>Year Level:</strong> <?php echo htmlspecialchars($section['year_level']); ?> YR</p>
                    <p><strong>Section Name:</strong> <?php echo htmlspecialchars($section['section_name']); ?></p>
                </div>

                <!-- Schedule Table -->
                <div class="bg-white shadow rounded p-4 mb-6">
                    <h3 class="text-center mb-4">Schedule</h3>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Day</th>
                                <th>Subject</th>
                                <th>Instructor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($schedule_result && $schedule_result->num_rows > 0) : ?>
                                <?php while ($row = $schedule_result->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['time']); ?></td>
                                        <td><?php echo htmlspecialchars($row['day']); ?></td>
                                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                        <td>
                                            <a href="faculty.php?faculty_id=<?php echo htmlspecialchars($row['faculty_id']); ?>" class="text-decoration-none text-primary">
                                                <?php echo htmlspecialchars($row['faculty_name']); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4" class="text-center">No schedule found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Student List -->
                <div class="bg-white shadow rounded p-4">
                    <h3 class="text-center mb-4">Student List</h3>
                    <ul class="list-group">
                        <?php if ($students_result && $students_result->num_rows > 0) : ?>
                            <?php while ($row = $students_result->fetch_assoc()) : ?>
                                <li class="list-group-item">
                                    <a href="students.php?student_id=<?php echo htmlspecialchars($row['student_id']); ?>" class="text-decoration-none text-primary">
                                        <?php echo htmlspecialchars($row['student_name']); ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <li class="list-group-item text-muted">No students found</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <button onclick="window.print()" class="btn btn-danger">Print</button>
                    <a href="index.php" class="btn btn-secondary">Back</a>
                </div>
            <?php endif; ?>
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

<?php
$conn->close();
?>
