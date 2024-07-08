<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $examId = $_GET['id'];
    $examinerId = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM exam_papers WHERE id = ? AND examiner_id = ?");
    $stmt->bind_param("ss", $examId, $examinerId);
    $stmt->execute();

    $response = [];
    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($response);
}
