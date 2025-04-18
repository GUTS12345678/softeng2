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
    if (isset($_POST['add_faculty'])) {
        $faculty_id = $conn->real_escape_string($_POST['faculty_id']);
        $faculty_name = $conn->real_escape_string($_POST['faculty_name']);
        $department = $conn->real_escape_string($_POST['department']);
        $query = "INSERT INTO faculty (faculty_id, faculty_name, department) VALUES ('$faculty_id', '$faculty_name', '$department')";
        if (!$conn->query($query)) {
            die("Error adding faculty: " . $conn->error);
        }
        header("Location: admin_faculty.php");
        exit;
    } elseif (isset($_POST['edit_faculty'])) { 
        $faculty_id = intval($_POST['faculty_id']);
        $faculty_name = $conn->real_escape_string($_POST['faculty_name']);
        $department = $conn->real_escape_string($_POST['department']);
        $query = "UPDATE faculty SET faculty_name = '$faculty_name', department = '$department' WHERE faculty_id = $faculty_id";
        if (!$conn->query($query)) {
            die("Error editing faculty: " . $conn->error);
        }
        header("Location: admin_faculty.php");
        exit;
    } elseif (isset($_POST['delete_faculty'])) {
        $faculty_id = intval($_POST['faculty_id']);

        // Delete related rows in the schedule table
        $query = "DELETE FROM schedule WHERE faculty_id = $faculty_id";
        if (!$conn->query($query)) {
            die("Error deleting related schedules: " . $conn->error);
        }

        // Delete the faculty member
        $query = "DELETE FROM faculty WHERE faculty_id = $faculty_id";
        if (!$conn->query($query)) {
            die("Error deleting faculty: " . $conn->error);
        }

        header("Location: admin_faculty.php");
        exit;
    }
}

// Fetch Faculty
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$query = "SELECT * FROM faculty WHERE faculty_name LIKE '%$search%' OR faculty_id LIKE '%$search%' ORDER BY faculty_name";
$result = $conn->query($query);

if (!$result) {
    die("Error in query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty</title>
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
            <h2 class="text-center mb-4">Manage Faculty</h2>

            <!-- Search Bar -->
            <form method="GET" class="d-flex mb-4 align-items-center">
                <input type="text" name="search" placeholder="Search faculty..." class="form-control me-2" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Search</button>
                <a href="admin_faculty.php" class="btn btn-secondary ms-2"><i class="fas fa-sync-alt"></i> Reset</a>
            </form>

            <!-- Add Faculty Button -->
            <div class="d-flex justify-content-end mb-4">
                <button class="btn btn-success" onclick="new bootstrap.Modal(document.getElementById('addFacultyModal')).show();">
                    <i class="fas fa-plus me-1"></i> Add Faculty
                </button>
            </div>

            <!-- Faculty Table -->
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row['faculty_id']; ?></td>
                            <td><?php echo $row['faculty_name']; ?></td>
                            <td><?php echo $row['department']; ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm me-1" 
                                        onclick="editFaculty('<?php echo $row['faculty_id']; ?>', 
                                                             '<?php echo $row['faculty_name']; ?>', 
                                                             '<?php echo $row['department']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="faculty_id" value="<?php echo $row['faculty_id']; ?>">
                                    <button type="submit" name="delete_faculty" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this faculty?')">
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

    <!-- Add Faculty Modal -->
    <div class="modal fade" id="addFacultyModal" tabindex="-1" aria-labelledby="addFacultyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addFacultyModalLabel">Add Faculty</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_faculty_id" class="form-label">Faculty ID</label>
                            <input type="text" name="faculty_id" id="add_faculty_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_faculty_name" class="form-label">Name</label>
                            <input type="text" name="faculty_name" id="add_faculty_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_department" class="form-label">Department</label>
                            <input type="text" name="department" id="add_department" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_faculty" class="btn btn-success">Add Faculty</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Faculty Modal -->
    <div class="modal fade" id="editFacultyModal" tabindex="-1" aria-labelledby="editFacultyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFacultyModalLabel">Edit Faculty</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_faculty_id" class="form-label">Faculty ID</label>
                            <input type="text" name="faculty_id" id="edit_faculty_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_faculty_name" class="form-label">Name</label>
                            <input type="text" name="faculty_name" id="edit_faculty_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_department" class="form-label">Department</label>
                            <select name="department" id="edit_department" class="form-select" required>
                                <option value="BSCS">BSCS</option>
                                <option value="BSIT">BSIT</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit_faculty" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editFaculty(id, name, department) {
            document.getElementById('edit_faculty_id').value = id;
            document.getElementById('edit_faculty_name').value = name;
            document.getElementById('edit_department').value = department;
            new bootstrap.Modal(document.getElementById('editFacultyModal')).show();
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>