<?php
include '../../connection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Prepare statement to find user with this token
    $stmt = $conn->prepare("SELECT * FROM regusers WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Update user to activate account
        $stmt = $conn->prepare("UPDATE regusers SET is_active = 1, token = NULL WHERE token = ?");
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            echo "Your account has been activated. You can now <a href='../../login/login.php'>login</a>.";
        } else {
            echo "Error activating account: " . $stmt->error;
        }
    } else {
        echo "Invalid activation token.";
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>
