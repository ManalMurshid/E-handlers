<?php
session_start(); // Start the session

// Destroy all session variables
$_SESSION = array();

// Destroy the session itself
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect to the login page or landing page
header("Location: login.php");
exit();
?>
