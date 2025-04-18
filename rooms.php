<?php
$conn = new mysqli("localhost", "root", "", "pc_tracking_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$result = null;
if ($search) {
    $query = "SELECT * FROM rooms WHERE room_name LIKE '%$search%' ORDER BY room_name";
    $result = $conn->query($query);
}

// Fetch room details if room_id is set
$room_id = isset($_GET['room_id']) ? $conn->real_escape_string($_GET['room_id']) : "";
$room_info = null;
$schedule = null;
if ($room_id) {
    $room_info_query = "SELECT * FROM rooms WHERE room_id = '$room_id'";
    $room_info_result = $conn->query($room_info_query);
    $room_info = $room_info_result->fetch_assoc();

    $schedule_query = "SELECT sch.time, sch.day, sub.subject_name, sub.subject_id, f.faculty_name, f.faculty_id, sec.section_name, sec.section_id 
                       FROM schedule sch
                       JOIN subjects sub ON sch.subject_id = sub.subject_id
                       JOIN faculty f ON sch.faculty_id = f.faculty_id
                       JOIN sections sec ON sch.section_id = sec.section_id
                       WHERE sch.room_id = '$room_id'";
    $schedule_result = $conn->query($schedule_query);
    $schedule = $schedule_result->fetch_all(MYSQLI_ASSOC);
}

// Search suggestions functionality
if (isset($_GET['suggestion'])) {
    $suggestion = $conn->real_escape_string($_GET['suggestion']);
    $suggestion_query = "SELECT room_id, room_name FROM rooms 
                         WHERE room_name LIKE '%$suggestion%' 
                         ORDER BY room_name LIMIT 5";
    $suggestion_result = $conn->query($suggestion_query);
    $suggestions = [];
    while ($row = $suggestion_result->fetch_assoc()) {
        $suggestions[] = $row;
    }
    echo json_encode($suggestions);
    exit;
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
            left: 20px; /* Moved to the left side */
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            z-index: 1100;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: left 0.3s ease; /* Smooth transition for button movement */
        }
        .toggle-btn.shifted {
            left: 270px; /* Push the button slightly to the right when the sidebar is open */
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
            <h2 class="text-center mb-4">Room List</h2>

            <!-- Search Bar -->
            <form method="GET" class="d-flex mb-4 position-relative">
                <input type="text" name="search" id="search" placeholder="Search by room name..." 
                    class="form-control me-2" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                <button type="submit" class="btn btn-danger text-white">
                    <i class="fas fa-search me-2"></i>Search
                </button>
                <div id="suggestions" class="position-absolute bg-white border rounded shadow w-100 mt-2 d-none"></div>
            </form>

            <!-- Room Table -->
            <?php if ($search && $result && $result->num_rows > 0) : ?>
                <div id="results" class="bg-white shadow rounded p-4">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="bg-light">
                                <th>Room Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) : ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="font-weight-bold">
                                        <a href="rooms.php?room_id=<?php echo htmlspecialchars($row['room_id']); ?>" class="text-primary text-decoration-none">
                                            <?php echo htmlspecialchars($row['room_name']); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($search) : ?>
                <p class="text-center text-muted">No rooms found.</p>
            <?php endif; ?>

            <!-- Room Information and Schedule -->
            <?php if ($room_info) : ?>
                <div id="room-info" class="bg-white shadow rounded p-4 mt-4">
                    <h3 class="mb-4">Room Profile</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($room_info['room_name']); ?></p>

                    <h3 class="mt-6 mb-4">Schedule</h3>
                    <table class="table table-bordered">
                        <thead>
                            <tr class="bg-light">
                                <th>Time</th>
                                <th>Day</th>
                                <th>Subject</th>
                                <th>Instructor</th>
                                <th>Section</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($schedule) : ?>
                                <?php foreach ($schedule as $class) : ?>
                                    <tr class="hover:bg-gray-100">
                                        <td><?php echo htmlspecialchars($class['time']); ?></td>
                                        <td><?php echo htmlspecialchars($class['day']); ?></td>
                                        <td><?php echo htmlspecialchars($class['subject_name']); ?></td>
                                        <td>
                                            <a href="faculty.php?faculty_id=<?php echo htmlspecialchars($class['faculty_id']); ?>" class="text-primary text-decoration-none">
                                                <?php echo htmlspecialchars($class['faculty_name']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="section.php?section_id=<?php echo htmlspecialchars($class['section_id']); ?>" class="text-primary text-decoration-none">
                                                <?php echo htmlspecialchars($class['section_name']); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="text-center">No schedule found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between mt-4">
                        <button onclick="window.print()" class="btn btn-danger text-white">Print</button>
                        <a href="rooms.php?search=<?php echo htmlspecialchars($search); ?>" class="btn btn-secondary text-white">Back</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#search').on('input', function() {
                var search = $(this).val();
                if (search.length > 0) {
                    // Fetch suggestions
                    $.get('rooms.php', { suggestion: search }, function(data) {
                        var suggestions = JSON.parse(data);
                        var suggestionHtml = suggestions.map(function(s) {
                            return `<div class="p-2 hover:bg-gray-200 cursor-pointer" onclick="selectSuggestion('${s.room_name}')">
                                        ${s.room_name}
                                    </div>`;
                        }).join('');
                        $('#suggestions').html(suggestionHtml).removeClass('d-none');
                    });

                    // Fetch search results
                    $.get('rooms.php', { search: search }, function(data) {
                        var html = $(data).find('#results').html();
                        $('#results').html(html);
                    });
                } else {
                    $('#results').html('');
                    $('#suggestions').addClass('d-none');
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#search, #suggestions').length) {
                    $('#suggestions').addClass('d-none');
                }
            });
        });

        function selectSuggestion(name) {
            $('#search').val(name);
            $('#suggestions').addClass('d-none');
            $('form').submit(); // Automatically submit the form
        }

        document.addEventListener("DOMContentLoaded", () => {
            const results = document.getElementById("results");
            const roomInfo = document.getElementById("room-info");
            if (results) {
                setTimeout(() => results.classList.add("visible"), 100);
            }
            if (roomInfo) {
                setTimeout(() => roomInfo.classList.add("visible"), 200);
            }
        });

        const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('visible');
            toggleSidebar.classList.toggle('shifted'); // Move the button when the sidebar is open
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('visible');
            toggleSidebar.classList.remove('shifted'); // Reset the button position
        });
    </script>

</body>
</html>

<?php
$conn->close();
?>
