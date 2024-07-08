<?php
include '../connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$exam_id = $_POST['exam_id'];

// Insert attempt into attempts table
$stmt = $conn->prepare("INSERT INTO attempts (user_id, exam_id) VALUES (?, ?)");
$stmt->bind_param("si", $user_id, $exam_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Attempt submitted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit attempt']);
}

$stmt->close();
$conn->close();
?>
