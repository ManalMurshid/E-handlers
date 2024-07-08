<?php
session_start();
include '../connection.php'; // Include your database connection file

// Initialize variables with default values
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; // Example value, replace it with session or login ID
$degree = '';
$batch = '';
$course_name = '';
$duration = '';
$exam_paper_link = '';

// Fetch user's full name from regusers table
$stmt = $conn->prepare("SELECT full_name FROM regusers WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();

// Check if there are parameters in the URL for editing
if (isset($_GET['edit'])) {
    // Fetch existing exam paper details from database based on id
    $exam_paper_id = $_GET['exam_id']; // Ensure 'id' is passed correctly
    $stmt = $conn->prepare("SELECT * FROM exam_papers WHERE exam_id = ?");
    $stmt->bind_param("i", $exam_paper_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If exam paper found, populate form fields
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Assign fetched values to variables
        $user_id = $row['user_id'];
        $degree = $row['degree'];
        $batch = $row['batch'];
        $course_name = $row['course_name'];
        $duration = $row['duration'];
        $exam_paper_link = $row['exam_paper_link'];
    }

    $stmt->close();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get other form inputs
    $user_id = $_POST['user_id'];
    $degree = $_POST['degree'];
    $batch = $_POST['batch'];
    $course_name = $_POST['course-name'];
    $duration = $_POST['duration'];
    $exam_paper_link = $_POST['exam-paper-link'];
    $exam_paper_id = $_POST['exam_id'];
    
    if (!empty($exam_paper_id)) {
        // Update existing exam paper in the database
        $exam_paper_id = $_POST['exam_id'];
        $stmt = $conn->prepare("UPDATE exam_papers SET user_id=?, degree=?, batch=?, course_name=?, duration=?, exam_paper_link=? WHERE exam_id=?");
        $stmt->bind_param("sssssss", $user_id, $degree, $batch, $course_name, $duration, $exam_paper_link, $exam_paper_id);
    } else {
        // Insert new exam paper into the database
        $stmt = $conn->prepare("INSERT INTO exam_papers (user_id, degree, batch, course_name, duration, exam_paper_link) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $user_id, $degree, $batch, $course_name, $duration, $exam_paper_link);
    }

    // Execute the statement
    if ($stmt->execute()) {
        // Set success message in session
        $_SESSION['success_message'] = "Exam paper saved successfully.";
        // Redirect back to create_exam.php (this same file)
        // header("Location: {$_SERVER['PHP_SELF']}");
        header("Location: {$_SERVER['PHP_SELF']}?edit=true&exam_id=$exam_paper_id");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam Paper</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="../sidebar-style.css">
    <!-- Include Toastr CSS and jQuery -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img style="width:150px" src="../img/logo.png" alt="ProctorOn Logo" class="logo">
            </div>
            <nav>
                <a href="../examiner_dashboard/examiner_dashboard.php" class="active">Home</a>
            </nav>
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
            <h1>Create Exam Paper</h1>
            <form class="exam-paper-form" action="create_exam.php" method="post">
                <div class="form-group">
                <label for="examiner-id">Examiner ID</label>
                    <span id="examiner-id"><?php echo htmlspecialchars($user_id); ?></span>
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                </div>
                <div class="form-group">
                    <label for="degree">Degree</label>
                    <select id="degree" name="degree">
                        <option value="BSc in Information Technology" <?php if ($degree == 'BSc in Information Technology') echo 'selected'; ?>>BSc in Information Technology</option>
                        <option value="BSc in Applied Biological Science" <?php if ($degree == 'BSc in Applied Biological Science') echo 'selected'; ?>>BSc in Applied Biological Science</option>
                        <option value="BSc in Applied Physical Science" <?php if ($degree == 'BSc in Applied Physical Science') echo 'selected'; ?>>BSc in Applied Physical Science</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="batch">Batch</label>
                    <select id="batch" name="batch">
                        <option value="18/19" <?php if ($batch == '18/19') echo 'selected'; ?>>18/19</option>
                        <option value="19/20" <?php if ($batch == '19/20') echo 'selected'; ?>>19/20</option>
                        <option value="20/21" <?php if ($batch == '20/21') echo 'selected'; ?>>20/21</option>
                        <option value="21/22" <?php if ($batch == '21/22') echo 'selected'; ?>>21/22</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="course-name">Course Name</label>
                    <input type="text" id="course-name" name="course-name" value="<?php echo htmlspecialchars($course_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="duration">Duration (in minutes)</label>
                    <input type="number" id="duration" name="duration" value="<?php echo htmlspecialchars($duration); ?>" required>
                </div>

                <div class="form-group">
                    <label for="exam-paper-link">Exam Paper Link</label>
                    <input type="text" id="exam-paper-link" name="exam-paper-link" value="<?php echo htmlspecialchars($exam_paper_link); ?>" required>
                </div>
                <!-- Hidden field to store exam_paper_id -->
                <input type="hidden" name="exam_id" value="<?php echo isset($exam_paper_id) ? htmlspecialchars($exam_paper_id) : ''; ?>">
                <div class="form-group">
                    <button type="submit">SAVE</button>
                </div>
            </form>
        </main>
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

    document.querySelector('.exam-paper-form').addEventListener('submit', function (event) {
        
        const googleFormLink = document.getElementById('exam-paper-link').value.trim();
        const durationInMinutes = document.getElementById('duration').value.trim();
        
        if (!isValidURL(googleFormLink)) {
            event.preventDefault();
            alert("Invalid Google Form Link format. Please enter a valid URL.");
            return;
        }

        if (!isValidDuration(durationInMinutes)) {
            event.preventDefault();
            alert("Invalid Duration. Please enter a valid number for the duration in minutes.");
            return;
        }

        console.log("I'm here");
    });

    function isValidURL(url) {
    // Regular expression for URL validation (same as before)
    const pattern = new RegExp(
        '^(https?:\\/\\/)?' + // protocol
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
        '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
        '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
        '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
        '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
    
    if (!pattern.test(url)) {
        return false; // URL format is invalid
    }
    
    // Check if it's a valid Google Form and belongs to your workspace
    if (!isValidGoogleForm(url)) {
        return false; // Google Form validation failed
    }
    
    return true; // URL is valid
    }

    function isValidDuration(duration) {
        // Validate if duration is a valid number
        if (isNaN(duration) || duration <= 0) {
            return false;
        }
        return true;
    }
    </script>
    <script>
        function editExam(examPaperId) {
        window.location.href = `../create_paper/create_exam.php?edit=true&id=${examPaperId}`;
        }
    </script>

</body>
</html>
