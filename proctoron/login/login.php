<?php
include '../connection.php';
session_start(); // Start the session

// Function to generate a random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare a SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM regusers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify password (use password_hash and password_verify for security)
        if (password_verify($password, $row['password'])) {

            // Generate a unique token
            $token = generateToken();                        
            // Store the token in the database or a temporary storage
            // For demonstration, we will store it in a session variable
            $_SESSION['mobile_login_token'] = $token;
            $_SESSION['mobile_login_email'] = $email;


            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['email'] = $row['email']; // Store other user info as needed
            $_SESSION['batch'] = $row['batch']; // Fetch and store the batch
            $_SESSION['degree'] = $row['degree']; // Fetch and store the degree
            
            $session_id = session_id();
            
            // Redirect based on role
            if ($row['role'] == 'candidate') {
                header("Location: ../candidate_dashboard/candidate_dashboard.php?session_id=$session_id");
            } else if ($row['role'] == 'examiner') {
                header("Location: ../examiner_dashboard/examiner_dashboard.php?session_id=$session_id");
            } else {
                // Handle other roles if needed
                // For now, redirect to a default dashboard
                header("Location: ../dashboard.php?session_id=$session_id");
            }
            exit(); // Ensure the script stops after redirection
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email.";
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
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="../img/logo.png" alt="ProctorOn Logo" class="logo">
            <img src="../img/2.jpg" alt="Illustration" class="illustration">
        </div>
        <div class="right">
            <div class="login-container">
                <h1>Login</h1>
                <p><a href="../Register/role.php">Sign Up</a> to Register</p>
                <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
                <form method="POST">
                    <label for="email">Please enter Email</label>
                    <input type="email" id="email" name="email" required>
                    <label for="password">Please enter password</label>
                    <input type="password" id="password" name="password" required>
                    <button type="submit">LOGIN</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>


