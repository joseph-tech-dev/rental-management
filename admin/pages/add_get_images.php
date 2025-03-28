<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '/opt/lampp/htdocs/project/config/db.php'; // Database connection

header('Content-Type: application/json'); // Ensure JSON response

// Fetch property images
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM property_images";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($images);
    exit;
}

// Add or Edit Property Image
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_id = $_POST['image_id'] ?? null;
    $property_id = $_POST['property_id'] ?? null;
    $image_url = null;

    if (!$property_id) {
        echo json_encode(["error" => "Property ID is required"]);
        exit;
    }

    // Handle file upload if an image is provided
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $target_dir = "../uploads/"; // Ensure this directory exists
        $image_name = time() . "_" . basename($_FILES['image_url']['name']);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES['image_url']['tmp_name'], $target_file)) {
            $image_url = "http://localhost/project/admin/uploads/" . $image_name; // Save relative path
        } else {
            echo json_encode(["error" => "File upload failed"]);
            exit;
        }
    } elseif (isset($_POST['image_url'])) {
        $image_url = $_POST['image_url'];
    } else {
        echo json_encode(["error" => "Image URL or file is required"]);
        exit;
    }

    try {
        if ($image_id) {
            // Update existing image
            $query = "UPDATE property_images SET property_id=:property_id, image_url=:image_url WHERE image_id=:image_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':image_id', $image_id);
        } else {
            // Insert new image
            $query = "INSERT INTO property_images (property_id, image_url) VALUES (:property_id, :image_url)";
            $stmt = $conn->prepare($query);
        }

        $stmt->bindParam(':property_id', $property_id);
        $stmt->bindParam(':image_url', $image_url);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Property image saved successfully"]);
        } else {
            echo json_encode(["error" => "Error saving property image"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

// Delete Property Image
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $image_id = $_DELETE['image_id'] ?? null;

    if (!$image_id) {
        echo json_encode(["error" => "Image ID is required"]);
        exit;
    }

    try {
        $query = "DELETE FROM property_images WHERE image_id = :image_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':image_id', $image_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => "Property image deleted successfully"]);
        } else {
            echo json_encode(["error" => "Error deleting property image"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

?>
