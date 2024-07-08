<?php
// Include database connection
include '../connection.php';
session_start(); // Start the session
// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Fetch the user ID from session
$user_id = $_SESSION['user_id'];
$batch = $_SESSION['batch']; // Get the batch from session
$degree = $_SESSION['degree']; // Get the degree from session

// Fetch user's full name from regusers table
$stmt = $conn->prepare("SELECT full_name FROM regusers WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Papers</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../sidebar-style.css">
</head>
<body>
    <div class="container">
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
        <main class="main-content">
            <h1>Exam Papers</h1>
            <div class="papers-grid">
                <?php
                
                $stmt = $conn->prepare("SELECT * FROM exam_papers WHERE batch = ? AND degree = ? AND exam_id NOT IN (SELECT exam_id FROM attempts WHERE user_id = ?) ORDER BY exam_id DESC");
                $stmt->bind_param("sss", $batch, $degree, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                ?>
                <main class="main-content">
                    
                    <div class="papers-grid">
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="paper">';
                            echo '<h2>' . htmlspecialchars($row['course_name']) . '</h2>';
                            echo '<p>Duration: ' . htmlspecialchars($row['duration']) . '</p>';
                            echo '<p>Batch: ' . htmlspecialchars($row['batch']) . '</p>';
                            echo '<a href="../input_full/index.php?exam_id=' . htmlspecialchars($row['exam_id']) . '" class="paper-button">Attempt</a>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </main>
                <?php
                } else {
                    // If no exam papers, display nothing
                    echo '<main class="main-content"><p>No exam papers found.</p></main>';
                }

                $stmt->close();
                $conn->close();
                ?>
            </div>
        </main>
        <div id="exam-container" style="display: none;">
            <iframe id="exam-iframe" style="width: 100%; height: 600px;"></iframe>
        </div>
    <div>
            <script>
                function attemptExam(examPaper) {
                    const iframe = document.getElementById('exam-iframe');
                    iframe.src = `../input_full/index.php?id=${examPaper}`;
                    document.getElementById('exam-container').style.display = 'block';
                    

                }
            </script>
        
    </div>
</body>
</html>
