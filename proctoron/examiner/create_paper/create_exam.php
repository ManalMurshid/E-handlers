<?php
session_start();
include '../../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = $_POST['course_name'];
    $duration = $_POST['duration'];
    $batch = $_POST['batch'];
    $examiner_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO exam_papers (course_name, duration, batch, examiner_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $course_name, $duration, $batch, $examiner_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header('Location: ../index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam Paper</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="main-content">
        <header>
            <h1>Create Exam Paper</h1>
        </header>
        <form method="POST" class="exam-form">
            <label for="course_name">Course Name:</label>
            <input type="text" id="course_name" name="course_name" required>
            <label for="duration">Duration:</label>
            <input type="text" id="duration" name="duration" required>
            <label for="batch">Batch:</label>
            <input type="text" id="batch" name="batch" required>
            <button type="submit">Create</button>
        </form>
    </div>
</body>
</html>
