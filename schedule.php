<?php
$conn = new mysqli("localhost", "root", "", "pc_tracking_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$query = "SELECT * FROM schedule WHERE subject_name LIKE '%$search%' ORDER BY schedule_id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <nav class="bg-[#a31d1d] text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between">
            <h1 class="text-xl font-bold">PC Tracking System</h1>
            <ul class="flex space-x-6">
                <li><a href="index.php" class="hover:text-gray-300">🏠 Home</a></li>

            </ul>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <h2 class="text-2xl font-bold mb-4">Class Schedule</h2>

        <div class="bg-white shadow-md rounded p-4">
            <table class="w-full border-collapse border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">Subject</th>
                        <th class="border p-2">Time</th>
                        <th class="border p-2">Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td class="border p-2"><?php echo htmlspecialchars($row['subject_name']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['time']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['room_number']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>

<?php
$conn->close();
?>
