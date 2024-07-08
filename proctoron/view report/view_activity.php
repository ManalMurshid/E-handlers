<?php
session_start();
include '../connection.php'; // Include your database connection file

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Fetch the user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user's full name from regusers table
$stmt = $conn->prepare("SELECT full_name FROM regusers WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();

// Fetch parameters from URL
$candidate_id = $_GET['candidate_id'];
$exam_id = $_GET['exam_id'];
$timestamp = urldecode($_GET['timestamp']); // Decode timestamp parameter

// Fetch candidate details from regusers table
$stmt = $conn->prepare("SELECT full_name, email FROM regusers WHERE user_id = ?");
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $candidate = $result->fetch_assoc();
    $candidate_name = $candidate['full_name'];
    $candidate_email = $candidate['email'];
} else {
    // Handle no results found scenario
    echo "Candidate not found.";
    exit();
}
$stmt->close();

// Fetch course name and batch from exam_papers table
$stmt = $conn->prepare("SELECT degree, batch, course_name FROM exam_papers WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $exam = $result->fetch_assoc();
    $course_name = $exam['course_name'];
    $batch = $exam['batch'];
} else {
    // Handle no results found scenario
    $course_name = "N/A";
    $batch = "N/A";
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Activity</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../sidebar-style.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img style="width:150px" src="../img/logo.png" alt="ProctorOn Logo" class="logo">
            </div>
            <nav>
                <a href="../examiner_dashboard/examiner_dashboard.php" class="active">Home</a>
            </nav>
            <div class="user-info">
                    <img img class="user-icon" src="../img/user.png" alt="User Icon">
                    <p class="user-text" >Hi, <?php echo htmlspecialchars($full_name); ?></p>
            </div>
            <div class="logout-button">
                <form action="../login/logout.php" method="POST">
                    <button type="submit">Logout</button>
                </form>
            </div>        
        </div>
        <div class="main-content">
            <h1>View Activity</h1>
            <div class="activity-details">
                <p><strong>Candidate ID:</strong> <?php echo htmlspecialchars($candidate_id); ?></p>
                <p><strong>Candidate Email:</strong> <?php echo htmlspecialchars($candidate_email); ?></p>
                <p><strong>Course Name:</strong> <?php echo htmlspecialchars($course_name); ?></p>
                <p><strong>Batch:</strong> <?php echo htmlspecialchars($batch); ?></p>
                <p><strong>Timestamp:</strong> <?php echo htmlspecialchars($timestamp); ?></p>
                <!-- Additional content related to viewing activity -->
            </div>
        </div>
    </div>
</body>
</html>
