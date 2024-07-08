<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'https://29f3-175-157-87-25.ngrok-free.app/proctoron/mobile_login/mobile-test.php', // Adjust to your domain
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax' // Or 'Strict'
]);
session_start();
include '../connection.php';

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Fetch user information from session
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$email = $_SESSION['mobile_login_email'];
$token = $_SESSION['mobile_login_token'];

$_SESSION['webcamAllowed'] = false;
$_SESSION['microphoneAllowed'] = false;
$_SESSION['secondaryCamAllowed'] = false;

// Fetch the full name of the user
$stmt = $conn->prepare("SELECT full_name FROM regusers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();

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
            <div class="user-info">
                <img src="../img/user.png" alt="User Icon">
                <p>Hi, <?php echo htmlspecialchars($full_name); ?></p>
            </div>
        </aside>
        <main class="main-content">
            <h1>Attempt Exam Paper</h1>
            <div class="exam-details">
                <?php echo $exam_details_html; ?>
            </div>
            <div class="status">
                <div class="status-item">
                    <img style="width:100px" src="../img/audio_2-removebg-preview.png" alt="Microphone">
                    <p id="microphone_input_text">NOT ALLOWED</p>
                </div>
                <div class="status-item">
                    <img style="width:120px" src="../img/web_cam-removebg-preview.png" alt="Primary Camera">
                    <p id="primary_camera_input_text">NOT ALLOWED</p>
                </div>
                <div class="status-item">
                    <img src="../img/web_cam-removebg-preview.png" alt="Secondary Camera">
                    <p id="secondary_camera_input_text">NOT ALLOWED</p>
                    <?php
                    if ($role == 'candidate') {
                        // Generate QR code URL
                        $url = "https://29f3-175-157-87-25.ngrok-free.app/proctoron/mobile_login/mobile-test.php?token=$token&exam_id=$exam_id&session_id=" . session_id();
                        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($url);

                        // Display the QR code
                        echo '<div id="qr-code-container" style="display:none" class="qr-code-container">';
                        echo '<p>Login through Mobile using the below QR code </p>';
                        echo '<img src="' . htmlspecialchars($qrCodeUrl) . '" alt="QR Code" class="qr-code-image" />';
                        echo '</div>';

                        // echo '<p>Debug Info:</p>';
                        // echo '<p>URL: ' . htmlspecialchars($url) . '</p>';
                    }
                    ?>
                </div>
            </div>
            <div id="warning" style="color: red; display: none;">All inputs must be allowed to proceed.</div>
            <div class="footer">
                <button id="startPermissionsButton" onclick="onAllowPermission()" >Allow Permissions</button>
                <button id="next" disabled class="disabled-next-button">NEXT</button>
            </div>
    </div>
    </main>
    <video id="videoElement" autoplay style="display: none;"></video>
    <canvas id="canvasElement" style="display: none;"></canvas>
    <audio id="audioElement" controls style="display: none;"></audio>
    <script src="scripts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('next').addEventListener('click', function () {
                window.location.href = 'google_form_page.php?exam_id=<?php echo $exam_id; ?>';
            });
        });
    </script>
</body>

</html>