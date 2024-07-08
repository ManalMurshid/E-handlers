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

// Fetch exam details (assuming you have stored them in a table)
$exam_id = $_GET['exam_id']; // Assuming you pass exam id through URL

$stmt = $conn->prepare("SELECT degree, batch, course_name FROM exam_papers WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $degree = $row['degree'];
    $course_name = $row['course_name'];
    $batch = $row['batch'];
} else {
    // Handle no results found scenario
    $degree = "N/A";
    $batch = "N/A";
    $course_name ="N/A";
}
$stmt->close();

// Fetch registered candidates' email addresses
$stmt = $conn->prepare("SELECT user_id, email FROM regusers WHERE role = 'candidate' AND batch = ? AND degree = ?");
$stmt->bind_param("ss", $batch, $degree);
$stmt->execute();
$result = $stmt->get_result();

$candidates = [];
while ($row = $result->fetch_assoc()) {
    $candidates[$row['user_id']] = $row['email'];
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Candidate</title>
    <link rel="stylesheet" href="styles.css">
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
        <main class="main-content">
            <h1>Select Candidate</h1>
            
            <form class="exam-paper-form" id="candidate-form" action="process_form.php" method="post">
                <div class="form-group">
                    <label for="examiner-id">Examiner ID</label>
                    <span id="examiner-id"><?php echo htmlspecialchars($user_id); ?></span>
                </div>
                <div class="form-group">
                    <label for="course-name">Course Name</label>
                    <span id="course-name"><?php echo htmlspecialchars($course_name); ?></span>
                </div>
                <div class="form-group">
                    <label for="batch">Batch</label>
                    <span id="batch"><?php echo htmlspecialchars($batch); ?></span>
                </div>
                <div class="form-group">
                    <label for="candidate-id">Select Candidate</label>
                    <select id="candidate-id" name="candidate_id" required>
                        <option value="">Select Candidate</option>
                        <?php foreach ($candidates as $id => $email) { ?>
                            <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($email); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit">VIEW</button>
                </div>
            </form>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>
