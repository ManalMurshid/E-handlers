<?php
include '../../connection.php';

$error_message = '';
$full_name = '';
$email = '';
$degree = '';
$batch = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['fullname'];
    $email = $_POST['email'];
    $degree = $_POST['degree'];
    $batch = $_POST['batch'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        // Check if email format is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Invalid email format.';
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT * FROM regusers WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error_message = 'This email is already registered.';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Default role (if you're setting a default role, you can skip the role field in the form)
                $role = 'candidate';

                // Prepare and bind
                $stmt = $conn->prepare("INSERT INTO regusers (full_name, email, password, degree, batch, role) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $full_name, $email, $hashed_password, $degree, $batch, $role);

                // Execute the statement
                if ($stmt->execute()) {
                    // Redirect to login.php after successful registration
                    header("Location: ../../login/login.php");
                    exit(); // Ensure that no further code is executed after redirection
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
            }

            $stmt->close();
        }
    }
}

// Close the database connection
$conn->close(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProctorOn - Sign Up</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error {
            color: red;
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #333;
            color: white;
            padding: 15px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
        }
        .toast.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-section">
            <img src="../../img/logo.png" alt="ProctorOn Logo" class="logo">
            <img src="../../img/2.jpg" alt="ProctorOn Illustration">
        </div>
        <div class="form-section">
            <h2>Create new Account</h2>
            <p>Already Registered? <a href="../../login/login.php">Login</a></p>

            <?php if (!empty($error_message)): ?>
                <div id="toast" class="toast show">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="candidate_reg.php" method="POST">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($full_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    <?php if (!empty($error_message) && strpos($error_message, 'email') !== false): ?>
                        <span class="error"><?php echo $error_message; ?></span>
                    <?php elseif (!empty($error_message) && strpos($error_message, 'format') !== false): ?>
                        <span class="error"><?php echo $error_message; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="degree">Degree</label>
                    <select id="degree" name="degree" required>
                        <option value="BSc in Information Technology" <?php if ($degree == 'BSc in Information Technology') echo 'selected'; ?>>BSc in Information Technology</option>
                        <option value="BSc in Applied Biological Science" <?php if ($degree == 'BSc in Applied Biological Science') echo 'selected'; ?>>BSc in Applied Biological Science</option>
                        <option value="BSc in Applied Physical Science" <?php if ($degree == 'BSc in Applied Physical Science') echo 'selected'; ?>>BSc in Applied Physical Science</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="batch">Batch</label>
                    <select id="batch" name="batch" required>
                        <option value="18/19" <?php if ($batch == '18/19') echo 'selected'; ?>>18/19</option>
                        <option value="19/20" <?php if ($batch == '19/20') echo 'selected'; ?>>19/20</option>
                        <option value="20/21" <?php if ($batch == '20/21') echo 'selected'; ?>>20/21</option>
                        <option value="21/22" <?php if ($batch == '21/22') echo 'selected'; ?>>21/22</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <?php if (!empty($error_message) && strpos($error_message, 'Passwords') !== false): ?>
                        <span class="error"><?php echo $error_message; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="submit-btn">SIGN UP</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        window.onload = function() {
            <?php if (!empty($error_message)): ?>
                var toast = document.getElementById("toast");
                toast.className = "toast show";
                setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
            <?php endif; ?>
        }
    </script>
</body>
</html>
