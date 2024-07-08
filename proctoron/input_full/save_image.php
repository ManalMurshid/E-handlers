<?php
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['image'])) {
    $image = $data['image'];
    $image = str_replace('data:image/jpeg;base64,', '', $image);
    $image = str_replace(' ', '+', $image);
    $imageData = base64_decode($image);

    date_default_timezone_set('Asia/Colombo');
    $timestamp = date("Ymd_His");
    $filename = "snapshots/$timestamp.jpg";
    $filepath = __DIR__ . '/' . $filename;

    if (!file_exists('snapshots')) {
        mkdir('snapshots', 0777, true);
    }

    file_put_contents($filepath, $imageData);

    echo "Saved: $filename";
} else {
    echo "Error: No image data received.";
}
?>
