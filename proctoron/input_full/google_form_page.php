<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attempt Exam Paper</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .exam-container {
            /* max-width: 800px; Adjust as needed */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .exam-container iframe {
            display: block;
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <img style="width:150px" src="../img/logo.png" alt="ProctorOn Logo" class="logo">
            </div>
            <nav>
                <a href="../candidate_dashboard/candidate_dashboard.php" class="active">Home</a>
            </nav>
            <div class="user-info">
                <img src="../img/user.png" alt="User Icon">
                <p>Hi, User</p>
            </div>
        </aside>
        <main class="main-content">
            <?php
            include '../connection.php';
            session_start();
            if (!isset($_SESSION['user_id'])) {
                header('Location: ../login/login.php');
                exit();
            }

            $user_id = $_SESSION['user_id'];
            $exam_id = $_GET['exam_id'];

            // Fetch the full name of the user
            $stmt = $conn->prepare("SELECT full_name FROM regusers WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($full_name);
            $stmt->fetch();
            $stmt->close();

            // Insert attempt into attempts table
            // $stmt = $conn->prepare("INSERT INTO attempts (user_id, exam_id) VALUES (?, ?)");
            // $stmt->bind_param("si", $user_id, $exam_id);
            // $stmt->execute();
            // $stmt->close();

            $stmt = $conn->prepare("SELECT * FROM exam_papers WHERE exam_id = ?");
            $stmt->bind_param("i", $exam_id); // Assuming exam_id is an integer
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                echo '<h1>' . htmlspecialchars($row['course_name']) . '</h1>';
                echo '<p><span class="label">Candidate ID:</span> ' . htmlspecialchars($user_id) . '</p>';
                echo '<p><span class="label">Duration:</span> ' . htmlspecialchars($row['duration']) . '</p>';
                echo '<p><span class="label">Timer:</span><span id="timer" class="timer-container"></span></p>';
                echo '<div class="exam-container">';
                echo '<iframe src="' . htmlspecialchars($row['exam_paper_link']) . '" width:"100%" height="100%" >Loading…</iframe>';
                echo '</div>';

            } else {
                echo '<p>Exam not found.</p>';
            }

            $stmt->close();
            $conn->close();
            ?>
            <div class="footer">
                <p style="color: red;">⚠️ Submit Google form before clicking the FINISH EXAM</p>
                <button id="finishExamButton" onclick="finish_exam()">FINISH EXAM</button>
            </div>
        </main>
    </div>
    <video id="videoElement" autoplay style="display: none;"></video>
    <canvas id="canvasElement" style="display: none;"></canvas>
    <audio id="audioElement" controls style="display: none;"></audio>
    <script src="scripts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            startCapturing();
        });
    </script>
    <script>
        $(document).ready(function () {
            var duration = <?php echo $row['duration']; ?>; // Duration in minutes
            initTimer(duration)
        });

        function finish_exam() {
            // Redirect to the candidate dashboard
            // window.location.href = '../candidate_dashboard/candidate_dashboard.php';
            // document.getElementById('finishExamForm').submit();

            var exam_id = <?php echo json_encode($exam_id); ?>; // Properly encode the PHP variable into JSON

                $.ajax({
                    url: 'finish_exam.php',
                    type: 'POST',
                    data: { exam_id: exam_id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Redirect to the candidate dashboard
                            window.location.href = '../candidate_dashboard/candidate_dashboard.php';
                        } else {
                            alert('Failed to submit attempt: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while submitting the attempt.');
                    }
                });
        }
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        fetchSessionVariables(); // Fetch initial session variables
        setInterval(fetchSessionVariables, 5000); // Periodically fetch session variables

        // Function to handle resuming activity after permissions are allowed
        function resumeActivity() {
            // Implement logic to resume activity here (e.g., enable form interactions)
            console.log('Resuming activity...');
            // Example: Enable form fields, continue timers, etc.
        }

        // Example: Check permissions before allowing activity
        const nextButton = document.getElementById('next');
        nextButton.addEventListener('click', function () {
            if (webcamAllowed && microphoneAllowed && secondaryCamAllowed) {
                // Proceed to next step (e.g., submit form)
                console.log('All permissions allowed. Proceeding...');
                resumeActivity(); // Call function to resume activity
            } else {
                // Display message indicating missing permissions
                alert('Please allow all permissions to proceed.');
            }
        });
    });
</script>

</body>

</html>