<?php
session_start();

// Handle AJAX request to set webcamAllowed in session
if (isset($_POST['webcamAllowed'])) {
    $_SESSION['webcamAllowed'] = $_POST['webcamAllowed'];
    exit('Webcam permission set successfully.');
}

// Handle AJAX request to set microphoneAllowed in session
if (isset($_POST['microphoneAllowed'])) {
    $_SESSION['microphoneAllowed'] = $_POST['microphoneAllowed'];
    exit('Microphone permission set successfully.');
}

// Handle AJAX request to set secondaryCamAllowed in session
if (isset($_POST['secondaryCamAllowed'])) {
    $_SESSION['secondaryCamAllowed'] = $_POST['secondaryCamAllowed'];
    exit('Secondat camera permission set successfully.');
}

exit('Invalid request');
?>
