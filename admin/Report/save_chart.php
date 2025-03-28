<?php
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['image'])) {
    echo json_encode(["success" => false, "message" => "No image data received."]);
    exit;
}

// Debug: Log the received image data
file_put_contents("debug_log.txt", $data['image']);

$image_data = explode(",", $data['image']);
if (count($image_data) !== 2) {
    echo json_encode(["success" => false, "message" => "Invalid image format."]);
    exit;
}

$image_base64 = base64_decode($image_data[1]);
$file_path = "Report/chart_image.png";

if (file_put_contents($file_path, $image_base64)) {
    echo json_encode(["success" => true, "message" => "Chart image saved.", "path" => $file_path]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save image."]);
}
?>
