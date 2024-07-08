<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Login</title>
    <link rel="stylesheet" href="styles.css">
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
                <img src="../img/user.jpg" alt="User Icon">
                <p>Hi, User</p>
            </div>
        </aside>
        <main class="main-content">
            <h1>Login through Mobile using the below QR code </h1>
            <div class="QR">
                <?php
                // error_reporting(E_ALL);
                // ini_set('display_errors', 1);

                // Session Regeneration,Token Expiry,and Secure Cookie Flags
                session_set_cookie_params([
                    'lifetime' => 0,
                    'path' => '/',
                    'domain' => 'https://a1f0-112-134-52-44.ngrok-free.app /phplogin/mobile_login/mobile.php', // Adjust to your domain
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Lax' // Or 'Strict'
                ]);
                session_start();

                // Redirect to login page if user is not logged in
                if (!isset($_SESSION['user_id'])) {
                    header("Location: login/login.php");
                    exit();
                }

                include '../connection.php';

                // Get user information from the session
                $user_id = $_SESSION['user_id'];
                $role = $_SESSION['role'];

                $token = $_SESSION['mobile_login_token'];
                $email = $_SESSION['mobile_login_email'];

                $session_id = session_id();

                echo "<script>console.log('Debug Objects: " . $token . "' );</script>";
                echo "<script>console.log('Debug Objects: " . $email . "' );</script>";

                if ($role == 'examiner') {
                    // echo "Welcome Examiner!";
                    // Examiner specific functionality
                } else if ($role == 'candidate') {
                    // echo "Welcome Candidate!";
                    // Candidate specific functionality

                    // Generate a unique token for the QR code
                    // $token = uniqid();
                    
                    // Save the token in the session
                    // $_SESSION['qr_token'] = $token;

                    // Generate the URL for the QR code
                    $url = "http://127.0.0.1:4040/phplogin/mobile_login/mobile.php?token=$token&session_id=$session_id";

                    // Use an alternative online service to generate the QR code
                    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($url);

                    // Display the QR code
                    echo '<div>';
                    echo '<img src="' . htmlspecialchars($qrCodeUrl) . '" alt="QR Code" />';
                    echo '</div>';

                    
                    echo '<p>Debug Info: </p>';
                    echo '<p>QR Code URL: ' . htmlspecialchars($qrCodeUrl) . '</p>';

                    echo '<p>Debug Info: </p>';
                    echo '<p>URL: ' . htmlspecialchars($url) . '</p>';
                }
                ?>
            </div>
            <div class="footer">
                <button id="nextButton">NEXT</button>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('nextButton').addEventListener('click', function() {
                        window.location.href = '../input_full/index.php';
                    });
                });
            </script> 
        </main>