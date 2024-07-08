<?php
// Include database connection
include '../connection.php';
session_start(); // Start the session

// Assuming the logged-in user's details are available in session or retrieved through login

// Fetch exam papers based on logged-in user's batch and degree
// Replace 'EXM2024156' with the actual logged-in user's examiner ID
$examiner_id = '';

$sql = "SELECT * FROM exam_papers WHERE user_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $examiner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
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
            <aside class="sidebar">
                    <div class="logo">
                    <img style="width:150px" src="../img/logo.png" alt="ProctorOn Logo" class="logo">
                    </div>
                    <a href="examiner_dashboard.php" class="home-link">Home</a>
                    <div class="user-info">
                        <img img style="width:50px"img style="margin-right: 10px" src="../img/user.jpg" alt="User Icon">
                        <p>Hi, User</p>
                    </div>
            </aside>
            <main class="main-content">
                <h1>Exam Papers</h1>
                <div class="papers-grid">
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="paper">';
                        echo '<h2>' . htmlspecialchars($row['course_name']) . '</h2>';
                        echo '<p>Duration: ' . htmlspecialchars($row['duration']) . '</p>';
                        echo '<p>Batch: ' . htmlspecialchars($row['batch']) . '</p>';
                        // Assuming 'googleFormLink' is a column in your 'exam_papers' table
                        // Construct the URL dynamically for each exam paper
                        $googleFormLink = htmlspecialchars($row['exam_paper_link']); // Replace with actual column name
                        echo '<a href="exam_page.php?form=' . urlencode($googleFormLink) . '" class="button" target="_blank">Attempt</a>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </main>
            <div>
                <!-- Optional: You can remove this iframe if it's not used -->
                <iframe id="exam_paper" src="" style="display: none;"></iframe>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    // If no exam papers, display nothing
    echo '<p>No exam papers found.</p>';
}

$stmt->close();
$conn->close();
?>
