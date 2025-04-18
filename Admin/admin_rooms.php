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
    if (isset($_POST['add_room'])) {
        $room_name = $conn->real_escape_string($_POST['room_name']);
        $floor = intval($_POST['floor']);
        $query = "INSERT INTO rooms (room_name, floor) VALUES ('$room_name', $floor)";
        if (!$conn->query($query)) {
            die("Error adding room: " . $conn->error);
        }
        header("Location: admin_rooms.php");
        exit;
    } elseif (isset($_POST['edit_room'])) {
        $room_id = intval($_POST['room_id']);
        $room_name = $conn->real_escape_string($_POST['room_name']);
        $floor = intval($_POST['floor']);
        $query = "UPDATE rooms SET room_name = '$room_name', floor = $floor WHERE room_id = $room_id";
        if (!$conn->query($query)) {
            die("Error editing room: " . $conn->error);
        }
        header("Location: admin_rooms.php");
        exit;
    } elseif (isset($_POST['delete_room'])) {
        $room_id = intval($_POST['room_id']);
        $query = "DELETE FROM rooms WHERE room_id = $room_id";
        if (!$conn->query($query)) {
            die("Error deleting room: " . $conn->error);
        }
        header("Location: admin_rooms.php");
        exit;
    }
}

// Fetch Rooms
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$query = "SELECT * FROM rooms WHERE room_name LIKE '%$search%' OR room_id LIKE '%$search%' ORDER BY room_name";
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
    <title>Manage Rooms</title>
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
            <h2 class="text-center mb-4">Manage Rooms</h2>

            <!-- Search Bar -->
            <form method="GET" class="d-flex mb-4 align-items-center">
                <input type="text" name="search" placeholder="Search rooms..." class="form-control me-2" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Search</button>
                <a href="admin_rooms.php" class="btn btn-secondary ms-2"><i class="fas fa-sync-alt"></i> Reset</a>
            </form>

            <!-- Add Room Button -->
            <div class="d-flex justify-content-end mb-4">
                <button class="btn btn-success" onclick="new bootstrap.Modal(document.getElementById('addRoomModal')).show();">
                    <i class="fas fa-plus me-1"></i> Add Room
                </button>
            </div>

            <!-- Rooms Table -->
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Room Name</th>
                        <th>Floor</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row['room_id']; ?></td>
                            <td><?php echo $row['room_name']; ?></td>
                            <td><?php echo $row['floor']; ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm me-1" 
                                        onclick="editRoom('<?php echo $row['room_id']; ?>', 
                                                          '<?php echo $row['room_name']; ?>', 
                                                          '<?php echo $row['floor']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="room_id" value="<?php echo $row['room_id']; ?>">
                                    <button type="submit" name="delete_room" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this room?')">
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

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRoomModalLabel">Add Room</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_room_name" class="form-label">Room Name</label>
                            <input type="text" name="room_name" id="add_room_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_floor" class="form-label">Floor</label>
                            <input type="number" name="floor" id="add_floor" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_room" class="btn btn-success">Add Room</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editRoomModalLabel">Edit Room</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="room_id" id="edit_room_id">
                        <div class="mb-3">
                            <label for="edit_room_name" class="form-label">Room Name</label>
                            <input type="text" name="room_name" id="edit_room_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_floor" class="form-label">Floor</label>
                            <input type="number" name="floor" id="edit_floor" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit_room" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editRoom(id, name, floor) {
            document.getElementById('edit_room_id').value = id;
            document.getElementById('edit_room_name').value = name;
            document.getElementById('edit_floor').value = floor;
            new bootstrap.Modal(document.getElementById('editRoomModal')).show();
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>