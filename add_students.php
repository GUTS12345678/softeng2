<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $section = $_POST['section'];

    $query = "INSERT INTO students (student_name, section_id) VALUES ('$name', '$section')";
    if ($conn->query($query)) {
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
    <title>Add Student</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6">
    <h2 class="text-2xl font-bold">Add Student</h2>
    <form method="POST" class="mt-4">
        <input type="text" name="name" placeholder="Student Name" required class="p-2 border rounded w-full">
        <input type="text" name="section" placeholder="Section ID" required class="p-2 border rounded w-full mt-2">
        <button type="submit" class="bg-green-500 text-white p-2 mt-4">Save</button>
    </form>
</body>
</html>
