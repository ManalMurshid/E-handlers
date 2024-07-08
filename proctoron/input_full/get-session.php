<?php
session_start();

// Fetch session variables
$webcamAllowed = isset($_SESSION['webcamAllowed']) ? $_SESSION['webcamAllowed'] : false;
$microphoneAllowed = isset($_SESSION['microphoneAllowed']) ? $_SESSION['microphoneAllowed'] : false;
$secondaryCamAllowed = isset($_SESSION['secondaryCamAllowed']) ? $_SESSION['secondaryCamAllowed'] : false;

// Return session variables as JSON
echo json_encode([
    'webcamAllowed' => $webcamAllowed,
    'microphoneAllowed' => $microphoneAllowed,
    'secondaryCamAllowed' => $secondaryCamAllowed,
]);
?>
