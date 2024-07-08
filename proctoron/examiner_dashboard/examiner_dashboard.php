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

// Fetch exam papers from the database
$stmt = $conn->prepare("SELECT exam_id, course_name, duration, batch FROM exam_papers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$papers = [];
while ($row = $result->fetch_assoc()) {
    $papers[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Papers</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../sidebar-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img style="width:150px" src="../img/logo.png" alt="ProctorOn Logo" class="logo">
        </div>
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
        <header>
            <h1>Exam Papers</h1>
            <button class="new-paper-btn" onclick="window.location.href='../create_paper/create_exam.php'">+ New Paper</button>
        </header>
        <div class="papers-container">
            <div class="papers-grid">
            <?php
                // Display exam papers
                foreach ($papers as $paper) {
                    echo '<div class="paper-card">';
                    echo '<h2>' . htmlspecialchars($paper['course_name']) . '</h2>';
                    echo '<p>Duration: ' . htmlspecialchars($paper['duration']) . '</p>';
                    echo '<p>Batch: ' . htmlspecialchars($paper['batch']) . '</p>';
                    echo '<div class="buttons">';
                    echo '<button onclick="editExam(' . htmlspecialchars(json_encode($paper['exam_id'])) . ')">Edit</button>';
                    echo '<button onclick="viewReport(' . htmlspecialchars(json_encode($paper['exam_id'])) . ')">View Reports</button>';
                    echo '<button onclick="deleteExam(' . htmlspecialchars(json_encode($paper['exam_id'])) . ')">Delete</button>';
                    echo '</div>';
                    echo '</div>';
                }
            ?>
            </div>
        </div>
    </div>

    
    
    <!-- Toastr Initialization -->
    <script>
    $(document).ready(function() {
        <?php
        // PHP session handling for Toastr success message
        if (isset($_SESSION['success_message'])) {
            echo 'toastr.success(' . json_encode($_SESSION['success_message']) . ');';
            unset($_SESSION['success_message']); // Clear the message after displaying it
        }
        ?>
    });
    </script>
    
    <script>
        function editExam(examPaperId) {
            window.location.href = `../create_paper/create_exam.php?edit=true&exam_id=${examPaperId}`;
        }

        function viewReport(examPaperId) {
            window.location.href = `../view report/view_reports.php?exam_id=${examPaperId}`;
        }

        function deleteExam(examPaperId) {
            if (confirm('Are you sure you want to delete this exam paper?')) {
                window.location.href = `../create_paper/delete_exam.php?exam_id=${examPaperId}`;

            }
        }
    </script>
</body>
</html>
