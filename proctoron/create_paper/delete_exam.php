<?php
session_start();
include '../connection.php'; // Include your database connection file

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Check if the id parameter is set
if (!isset($_GET['exam_id'])) {
    // Redirect to the exam papers page with an error message
    $_SESSION['error_message'] = "Invalid request. No exam paper ID provided.";
    header('Location: ../examiner_dashboard/examiner_dashboard.php');
    exit();
}

$exam_paper_id = $_GET['exam_id'];

// Prepare the delete statement
$stmt = $conn->prepare("DELETE FROM exam_papers WHERE exam_id = ? AND user_id = ?");
$stmt->bind_param("ii", $exam_paper_id, $_SESSION['user_id']);

// Execute the statement
if ($stmt->execute()) {
    // Set success message in session
    $_SESSION['success_message'] = "Exam paper deleted successfully.";
} else {
    // Set error message in session
    $_SESSION['error_message'] = "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirect back to the exam papers page
header('Location: ../examiner_dashboard/examiner_dashboard.php');
exit();
?>
