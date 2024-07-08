<?php
session_start();
include 'connection.php';

$examiner_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, course_name, duration, batch FROM exam_papers WHERE examiner_id = ?");
$stmt->bind_param("s", $examiner_id);
$stmt->execute();
$result = $stmt->get_result();

$papers = [];
while ($row = $result->fetch_assoc()) {
    $papers[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($papers);
