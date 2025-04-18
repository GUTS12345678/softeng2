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
    if (isset($_POST['add_section'])) {
        $section_name = $conn->real_escape_string($_POST['section_name']);
        $year_level = intval($_POST['year_level']);
        $course_id = intval($_POST['course_id']);
        $query = "INSERT INTO sections (section_name, year_level, course_id) VALUES ('$section_name', $year_level, $course_id)";
        if (!$conn->query($query)) {
            die("Error adding section: " . $conn->error);
        }
        header("Location: admin_sections.php");
        exit;
    } elseif (isset($_POST['edit_section'])) {
        $section_id = intval($_POST['section_id']);
        $section_name = $conn->real_escape_string($_POST['section_name']);
        $year_level = intval($_POST['year_level']);
        $course_id = intval($_POST['course_id']);
        $query = "UPDATE sections SET section_name = '$section_name', year_level = $year_level, course_id = $course_id WHERE section_id = $section_id";
        if (!$conn->query($query)) {
            die("Error editing section: " . $conn->error);
        }
        header("Location: admin_sections.php");
        exit;
    } elseif (isset($_POST['delete_section'])) {
        $section_id = intval($_POST['section_id']);
        $query = "DELETE FROM sections WHERE section_id = $section_id";
        if (!$conn->query($query)) {
            die("Error deleting section: " . $conn->error);
        }
        header("Location: admin_sections.php");
        exit;
    }
}

// Fetch Sections
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$query = "SELECT s.section_id, s.section_name, s.year_level, s.course_id, c.course_name 
          FROM sections s 
          LEFT JOIN courses c ON s.course_id = c.course_id 
          WHERE s.section_name LIKE '%$search%' OR c.course_name LIKE '%$search%' 
          ORDER BY s.section_name";
$result = $conn->query($query);

if (!$result) {
    die("Error in query: " . $conn->error);
}

// Fetch Courses for Dropdown
$courses = $conn->query("SELECT course_id, course_name FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sections</title>
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
            <h2 class="text-center mb-4">Manage Sections</h2>

            <!-- Search Bar -->
            <form method="GET" class="d-flex mb-4 align-items-center">
                <input type="text" name="search" placeholder="Search sections..." class="form-control me-2" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Search</button>
                <a href="admin_sections.php" class="btn btn-secondary ms-2"><i class="fas fa-sync-alt"></i> Reset</a>
            </form>

            <!-- Add Section Button -->
            <div class="d-flex justify-content-end mb-4">
                <button class="btn btn-success" onclick="new bootstrap.Modal(document.getElementById('addSectionModal')).show();">
                    <i class="fas fa-plus me-1"></i> Add Section
                </button>
            </div>

            <!-- Sections Table -->
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Section Name</th>
                        <th>Year Level</th>
                        <th>Course</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row['section_id']; ?></td>
                            <td><?php echo $row['section_name']; ?></td>
                            <td><?php echo $row['year_level']; ?></td>
                            <td><?php echo $row['course_name']; ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm me-1" 
                                        onclick="editSection('<?php echo $row['section_id']; ?>', 
                                                             '<?php echo $row['section_name']; ?>', 
                                                             '<?php echo $row['year_level']; ?>', 
                                                             '<?php echo $row['course_id']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="section_id" value="<?php echo $row['section_id']; ?>">
                                    <button type="submit" name="delete_section" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this section?')">
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

    <!-- Add Section Modal -->
    <div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSectionModalLabel">Add Section</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_section_name" class="form-label">Section Name</label>
                            <input type="text" name="section_name" id="add_section_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_year_level" class="form-label">Year Level</label>
                            <input type="number" name="year_level" id="add_year_level" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_course_id" class="form-label">Course</label>
                            <select name="course_id" id="add_course_id" class="form-select" required>
                                <option value="1">BSCS</option>
                                <option value="2">BSIT</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_section" class="btn btn-success">Add Section</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Section Modal -->
    <div class="modal fade" id="editSectionModal" tabindex="-1" aria-labelledby="editSectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editSectionModalLabel">Edit Section</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="section_id" id="edit_section_id">
                        <div class="mb-3">
                            <label for="edit_section_name" class="form-label">Section Name</label>
                            <input type="text" name="section_name" id="edit_section_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_year_level" class="form-label">Year Level</label>
                            <input type="number" name="year_level" id="edit_year_level" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_course_id" class="form-label">Course</label>
                            <select name="course_id" id="edit_course_id" class="form-select" required>
                                <option value="1">BSCS</option>
                                <option value="2">BSIT</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit_section" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editSection(id, name, year, courseId) {
            document.getElementById('edit_section_id').value = id;
            document.getElementById('edit_section_name').value = name;
            document.getElementById('edit_year_level').value = year;
            document.getElementById('edit_course_id').value = courseId;
            new bootstrap.Modal(document.getElementById('editSectionModal')).show();
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>