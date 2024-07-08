<?php
// Include the database connection file
include '../../connection.php';

// Define variables for error messages
$password_error = '';
$email_error = '';

$full_name = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Only take necessary inputs
    $full_name = isset($_POST['fullname']) ? $_POST['fullname'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validate password match
    if ($password !== $confirm_password) {
        $password_error = "Passwords do not match.";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format.";
    }

    // Check if email already exists
    $stmt_check_email = $conn->prepare("SELECT email FROM regusers WHERE email = ?");
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows > 0) {
        $email_error = "Email already exists. Please use a different email.";
    }

    // If no errors, proceed with registration
    if (empty($password_error) && empty($email_error)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Set the role
        $role = 'Examiner';

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO regusers (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);

        // Execute the statement
        if ($stmt->execute()) {
            header("Location: ../../login/login.php");
            exit();
        } else {
            echo "Error: Registration failed.";
        }

        $stmt->close();
    }

    $stmt_check_email->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error {
            color: red;
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
            <form action="examiner_reg.php" method="POST">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" required value="<?php echo htmlspecialchars($full_name); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                    <?php if (!empty($email_error)) { ?>
                        <span class="error"><?php echo $email_error; ?></span>
                    <?php } ?>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <?php if (!empty($password_error)) { ?>
                        <span class="error"><?php echo $password_error; ?></span>
                    <?php } ?>
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
</body>
</html>
