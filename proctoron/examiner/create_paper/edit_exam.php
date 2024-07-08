<?php
session_start();
include '../connection.php';

if (isset($_GET['id'])) {
    $examId = $_GET['id'];
    $examinerId = $_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $course_name = $_POST['course_name'];
        $duration = $_POST['duration'];
        $batch = $_POST['batch'];

        $stmt = $conn->prepare("UPDATE exam_papers SET course_name = ?, duration = ?, batch = ? WHERE id = ? AND examiner_id = ?");
        $stmt->bind_param("sssss", $course_name, $duration, $batch, $examId, $examinerId);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        header('Location: ../index.html');
    } else {
        $stmt = $conn->prepare("SELECT course_name, duration, batch FROM exam_papers WHERE id = ? AND examiner_id = ?");
        $stmt->bind_param("ss", $examId, $examinerId);
        $stmt->execute();
        $result = $stmt->get_result();
        $paper = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
    }
} else {
    header('Location: ../index.html');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exam Paper</title>
</head>
<body>
    <h1>Edit Exam Paper</h1>
    <form method="POST">
        <label for="course_name">Course Name:</label>
        <input type="text" id="course_name" name="course_name" value="<?php echo htmlspecialchars($paper['course_name']); ?>" required>
        <label for="duration">Duration:</label>
        <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($paper['duration']); ?>" required>
        <label for="batch">Batch:</label>
        <input type="text" id="batch" name="batch" value="<?php echo htmlspecialchars($paper['batch']); ?>" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>
