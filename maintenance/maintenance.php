<?php

session_start();
// Debug: Check session values
//print_r($_SESSION);

// Include database connection
include '../config/db.php'; 

// Ensure database connection is valid
if (!$conn) {
    die("Database connection failed.");
}

// Ensure user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    die("Unauthorized access");
}

$user_id = $_SESSION['user_id'];

// Fetch tenant's property ID
$query = "SELECT property_id FROM tenants WHERE user_id = :user_id";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed.");
}

$stmt->execute(['user_id' => $user_id]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tenant) {
    die("No property assigned to this tenant.");
}

$property_id = $tenant['property_id'];

// Handle new maintenance request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['description'])) {
    $description = trim($_POST['description']);
    
    if (!empty($description)) {
        $insertQuery = "INSERT INTO maintenance_requests (tenant_id, property_id, description) VALUES (:tenant_id, :property_id, :description)";
        $stmt = $conn->prepare($insertQuery);
        
        if (!$stmt) {
            die("Prepare failed.");
        }

        if ($stmt->execute([
            'tenant_id' => $user_id,
            'property_id' => $property_id,
            'description' => $description
        ])) {
            echo "<p style='color: green;'>Request submitted successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error submitting request.</p>";
        }
    } else {
        echo "<p style='color: red;'>Description cannot be empty.</p>";
    }
}

// Fetch maintenance requests for logged-in tenant
$query = "SELECT * FROM maintenance_requests WHERE tenant_id = :tenant_id ORDER BY created_at DESC";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed.");
}

$stmt->execute(['tenant_id' => $user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Include header file (adjust the path as needed)
include '../includes/header.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Requests</title>
    <style>
        /* Reset default margins and paddings */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Make the body take the full viewport height and use flexbox */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        /* Header styling */
        header {
            
            color: white;
            text-align: center;
            padding: 15px;
            font-size: 24px;
            font-weight: bold;
        }

        /* Main content container */
        .container {
            flex: 1; /* Makes sure it takes up all available space */
            width: 80%;
            margin: auto;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background: #007bff;
            color: white;
        }

        /* Form styling */
        .form-container {
            margin-top: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        textarea {
            width: 100%;
            height: 80px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }

        /* Submit button styling */
        .btn {
            background: #28a745;
            color: white;
            padding: 12px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            display: block;
            margin: 10px auto;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #218838;
        }

        /* Footer styling */
        footer {
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: auto; /* Pushes the footer to the bottom */
        }

    </style>
</head>
<body>
    <h2>Your Maintenance Requests</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Description</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Updated At</th>
        </tr>
        <?php foreach ($requests as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['request_id']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td><?= htmlspecialchars($row['updated_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Submit a New Request</h3>
    <form method="POST">
        <textarea name="description" required></textarea>
        <br>
        <button type="submit" class="btn">Submit Request</button>
    </form>
</body>
</html>
<?php include '../includes/footer.php'; ?>
