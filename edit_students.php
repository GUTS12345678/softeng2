<?php
include 'db_connect.php';

$id = $_GET['id'];
$query = "SELECT * FROM students WHERE student_id = '$id'";
$result = $conn->query($query);
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $section = $_POST['section'];

    $updateQuery = "UPDATE students SET student_name='$name', section_id='$section' WHERE student_id='$id'";
    if ($conn->query($updateQuery)) {
        header("Location: students.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Student</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
    <h2 class="text-2xl font-bold">Edit Student</h2>
    <form method="POST" class="mt-4">
        <input type="text" name="name" value="<?php echo $row['student_name']; ?>" required class="p-2 border rounded w-full">
        <input type="text" name="section" value="<?php echo $row['section_id']; ?>" required class="p-2 border rounded w-full mt-2">
        <button type="submit" class="bg-blue-500 text-white p-2 mt-4">Update</button>
    </form>
</body>
</html>
