<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "pc_tracking_system");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_student'])) {
        $student_id = $conn->real_escape_string($_POST['student_id']);
        $student_name = $conn->real_escape_string($_POST['student_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $section_id = intval($_POST['section_id']);
        $course_id = intval($_POST['course_id']);
        $query = "INSERT INTO students (student_id, student_name, email, section_id, course_id, status) 
                  VALUES ('$student_id', '$student_name', '$email', $section_id, $course_id, 'Active')";
        if (!$conn->query($query)) {
            die("Error adding student: " . $conn->error);
        }
        header("Location: admin_students.php");
        exit;
    } elseif (isset($_POST['edit_student'])) {
        $student_id = $conn->real_escape_string($_POST['student_id']);
        $student_name = $conn->real_escape_string($_POST['student_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $section_id = intval($_POST['section_id']);
        $course_id = intval($_POST['course_id']);
        $query = "UPDATE students SET student_id = '$student_id', student_name = '$student_name', email = '$email', 
                  section_id = $section_id, course_id = $course_id WHERE student_id = '$student_id'";
        if (!$conn->query($query)) {
            die("Error editing student: " . $conn->error);
        }
        header("Location: admin_students.php");
        exit;
    } elseif (isset($_POST['delete_student'])) {
        $student_id = $conn->real_escape_string($_POST['student_id']);
        $query = "DELETE FROM students WHERE student_id = '$student_id'";
        if (!$conn->query($query)) {
            die("Error deleting student: " . $conn->error);
        }

        // Redirect to avoid form resubmission
        header("Location: admin_students.php");
        exit;
    }
}

// Fetch Students
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$query = "SELECT s.student_id, s.student_name, s.email, sec.section_id, sec.section_name, c.course_id, c.course_name, s.status 
          FROM students s 
          LEFT JOIN sections sec ON s.section_id = sec.section_id 
          LEFT JOIN courses c ON sec.course_id = c.course_id
          WHERE s.student_name LIKE '%$search%' OR s.student_id LIKE '%$search%' 
          ORDER BY s.student_name";
$result = $conn->query($query);

if (!$result) {
    die("Error in query: " . $conn->error);
}

// Fetch Sections and Courses for Dropdown
$sections = $conn->query("SELECT section_id, section_name FROM sections");
$courses = $conn->query("SELECT course_id, course_name FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
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
            margin-left: 250px; /* Adjust content margin to account for the sidebar width */
            padding: 20px;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.3em 0.6em;
            border-radius: 0.5rem;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body class="bg-light">
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
            <h2 class="text-center mb-4">Manage Students</h2>

            <!-- Search Bar -->
            <form method="GET" class="d-flex mb-4 align-items-center">
                <input type="text" name="search" placeholder="Search students..." class="form-control me-2" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Search</button>
                <a href="admin_students.php" class="btn btn-secondary ms-2"><i class="fas fa-sync-alt"></i> Reset</a>
            </form>

            <!-- Add Student Button -->
            <div class="d-flex justify-content-end mb-4">
                <button class="btn btn-success" onclick="new bootstrap.Modal(document.getElementById('addStudentModal')).show();">
                    <i class="fas fa-plus me-1"></i> Add Student
                </button>
            </div>

            <!-- Students Table -->
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row['student_id']; ?></td>
                            <td>
                                <strong><?php echo $row['student_name']; ?></strong><br>
                                <small class="text-muted"><?php echo $row['email']; ?></small>
                            </td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['course_name']; ?></td>
                            <td><?php echo $row['section_name']; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-warning btn-sm me-1" 
                                        onclick="editStudent('<?php echo $row['student_id']; ?>', 
                                                             '<?php echo $row['student_name']; ?>', 
                                                             '<?php echo $row['email']; ?>', 
                                                             '<?php echo $row['section_id']; ?>', 
                                                             '<?php echo $row['course_id']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                                    <button type="submit" name="delete_student" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStudentModalLabel">Add Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_student_id" class="form-label">Student ID</label>
                            <input type="text" name="student_id" id="add_student_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_student_name" class="form-label">Name</label>
                            <input type="text" name="student_name" id="add_student_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_email" class="form-label">Email</label>
                            <input type="email" name="email" id="add_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_section_id" class="form-label">Section</label>
                            <select name="section_id" id="add_section_id" class="form-select" required>
                                <?php while ($section = $sections->fetch_assoc()) : ?>
                                    <option value="<?php echo $section['section_id']; ?>"><?php echo $section['section_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="add_course_id" class="form-label">Course</label>
                            <select name="course_id" id="add_course_id" class="form-select" required>
                                <?php while ($course = $courses->fetch_assoc()) : ?>
                                    <option value="<?php echo $course['course_id']; ?>"><?php echo $course['course_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_student" class="btn btn-success">Add Student</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_student_id" class="form-label">Student ID</label>
                            <input type="text" name="student_id" id="edit_student_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_student_name" class="form-label">Name</label>
                            <input type="text" name="student_name" id="edit_student_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_section_id" class="form-label">Section</label>
                            <select name="section_id" id="edit_section_id" class="form-select" required>
                                <?php while ($section = $sections->fetch_assoc()) : ?>
                                    <option value="<?php echo $section['section_id']; ?>"><?php echo $section['section_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_course_id" class="form-label">Course</label>
                            <select name="course_id" id="edit_course_id" class="form-select" required>
                                <?php while ($course = $courses->fetch_assoc()) : ?>
                                    <option value="<?php echo $course['course_id']; ?>"><?php echo $course['course_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit_student" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editStudent(id, name, email, sectionId, courseId) {
            document.getElementById('edit_student_id').value = id;
            document.getElementById('edit_student_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_section_id').value = sectionId;
            document.getElementById('edit_course_id').value = courseId || ''; // Handle null values
            new bootstrap.Modal(document.getElementById('editStudentModal')).show();
        }
    </script>
</body>
</html>