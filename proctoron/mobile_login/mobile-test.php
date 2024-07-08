<?php
// Check if session_id is provided in the URL
if (isset($_GET['session_id'])) {
    // Set session ID from the URL parameter
    session_id($_GET['session_id']);
}

// Start or resume the session
session_start();
include '../connection.php';


// Fetch user's full name from regusers table
$stmt = $conn->prepare("SELECT full_name FROM regusers WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();


// Now you can access session variables
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    $email = $_SESSION['email'];
    $batch = $_SESSION['batch'];
    $degree = $_SESSION['degree'];

    // Display or use session variables as needed
    // echo "User ID: $user_id<br>";
    // echo "Role: $role<br>";
    // echo "Email: $email<br>";
    // echo "Batch: $batch<br>";
    // echo "Degree: $degree<br>";
} else {
    // Handle if session is not valid or user not authenticated
    // echo "Session not found or user not authenticated.";
}


// Fetch exam details based on exam_id from URL parameter
if (isset($_GET['exam_id'])) {
    $exam_id = $_GET['exam_id'];

    $stmt = $conn->prepare("SELECT * FROM exam_papers WHERE exam_id = ?");
    $stmt->bind_param("s", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $course_name = htmlspecialchars($row['course_name']);
        $duration = htmlspecialchars($row['duration']);
        $batch = htmlspecialchars($row['batch']);

        // Output exam details
        $exam_details_html = "
                <p><span class='label'>Exam:</span> $course_name</p>
                <p><span class='label'>Duration:</span> $duration</p>
                <p><span class='label'>Batch:</span> $batch</p>
                <p><span class='label'>Candidate ID:</span> $user_id</p>
            ";
    } else {
        $exam_details_html = "<p>Exam not found.</p>";
    }

    $stmt->close();
} else {
    $exam_details_html = "<p>Exam ID not specified.</p>";
}

$conn->close();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attempt Exam Paper</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../sidebar-style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <img style="width:150px" src="../img/logo.png" alt="ProctorOn Logo" class="logo">
            </div>
            <nav>
                <a href="../candidate_dashboard/candidate_dashboard.php" class="active">Home</a>
            </nav>
            <!-- <div class="user-info">
                <img src="../img/user.png" alt="User Icon">
                <p>Hi,></p>
            </div> -->
        </aside>
        <main class="main-content">
            <h1>Attempt Exam Paper</h1>
            <div class="exam-details">
                <?php echo $exam_details_html; ?>
            </div>
            <div class="status">
                <div class="status-item">
                    <img src="../img/web_cam-removebg-preview.png" alt="Secondary Camera">
                    <p id="secondary_camera_input_text">NOT ALLOWED</p>
                </div>
            </div>
            <div id="warning" style="color: red; display: none;">All inputs must be allowed to proceed.</div>
            <div class="footer">
                <button id="startPermissionsButton">Allow Permissions</button>
            </div>
    </div>
    </main>
    <video id="videoElement" autoplay style="display: none;"></video>
    <canvas id="canvasElement" style="display: none;"></canvas>
    <audio id="audioElement" controls style="display: none;"></audio>
    <script src="scripts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('startPermissionsButton').addEventListener('click', function () {
                startWebcam();
            });
        });
    </script>
</body>

</html>