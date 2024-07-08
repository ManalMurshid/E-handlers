<?php
if (isset($_FILES['audio'])) {
    $audio = $_FILES['audio'];
    date_default_timezone_set('Asia/Colombo');
    $timestamp = date("Ymd_His");
    $filename = "audio/$timestamp.mp3";
    $filepath = __DIR__ . '/' . $filename;

    if (!file_exists('audio')) {
        mkdir('audio', 0777, true);
    }

    if (move_uploaded_file($audio['tmp_name'], $filepath)) {
        echo "Saved: $filename";
    } else {
        echo "Error: Could not save file.";
    }
} else {
    echo "Error: No audio file received.";
}
?>
