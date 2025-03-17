<?php

session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include '/opt/lampp/htdocs/project/config/db.php'; 

// Ensure database connection is valid
if (!$conn) {
    die("Database connection failed.");
}

// Ensure user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'tenant') {
    die("Unauthorized access");
}

$user_id = $_SESSION['user_id'];

// Fetch tenant's lease details
$query = "SELECT l.lease_id, l.start_date, l.end_date, l.status, p.name AS property_name, p.address, p.rent_amount
          FROM leases l
          JOIN tenants t ON l.tenant_id = t.tenant_id
          JOIN properties p ON l.property_id = p.property_id
          WHERE t.user_id = :user_id";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed.");
}

$stmt->execute(['user_id' => $user_id]);
$lease = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lease) {
    die("No lease found for this tenant.");
}

// Include header file (adjust the path as needed)
include '../includes/header.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lease Details</title>
    <style>
       /* Reset default margins and paddings */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Lease Details</h2>
        <table>
            <tr>
                <th>Lease ID</th>
                <td><?= htmlspecialchars($lease['lease_id']) ?></td>
            </tr>
            <tr>
                <th>Property Name</th>
                <td><?= htmlspecialchars($lease['property_name']) ?></td>
            </tr>
            <tr>
                <th>Property Address</th>
                <td><?= htmlspecialchars($lease['address']) ?></td>
            </tr>
            <tr>
                <th>Rent Amount</th>
                <td>$<?= htmlspecialchars($lease['rent_amount']) ?></td>
            </tr>
            <tr>
                <th>Start Date</th>
                <td><?= htmlspecialchars($lease['start_date']) ?></td>
            </tr>
            <tr>
                <th>End Date</th>
                <td><?= htmlspecialchars($lease['end_date']) ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?= htmlspecialchars($lease['status']) ?></td>
            </tr>
        </table>
    </div>
</body>
</html>

<?php include '/opt/lampp/htdocs/project/includes/footer.php'; ?>
