<?php
include 'db_connect.php';

$id = $_GET['id'];
$query = "DELETE FROM students WHERE student_id = '$id'";
if ($conn->query($query)) {
    header("Location: students.php");
    exit();
} else {
    echo "Error: " . $conn->error;
}
?>
