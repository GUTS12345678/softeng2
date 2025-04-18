<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "pc_tracking_system");

$id = intval($_GET['id']);
$query = "SELECT * FROM admin WHERE admin_id = $id";
$result = $conn->query($query);
$admin = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $full_name = $conn->real_escape_string($_POST['full_name']);

    $query = "UPDATE admin SET username = '$username', full_name = '$full_name' WHERE admin_id = $id";
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
    <title>Edit Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="text-center mb-4">Edit Admin</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" value="<?php echo $admin['username']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo $admin['full_name']; ?>" required>
            </div>
            <button type="submit" class="btn btn-warning w-100">Update Admin</button>
        </form>
    </div>
</body>
</html>