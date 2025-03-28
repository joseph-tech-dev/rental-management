<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('/opt/lampp/htdocs/project/fpdf.php'); // Include FPDF for PDF generation
require('/opt/lampp/htdocs/project/config/db.php'); // Database connection

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(190, 10, 'Tenant Rental Report', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Fetch data for reports
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch(PDO::FETCH_ASSOC)['total'];
$totalProperties = $conn->query("SELECT COUNT(*) AS total FROM properties")->fetch(PDO::FETCH_ASSOC)['total'];
$totalTenants = $conn->query("SELECT COUNT(*) AS total FROM tenants")->fetch(PDO::FETCH_ASSOC)['total'];
$totalPayments = $conn->query("SELECT SUM(amount) AS total FROM payments WHERE payment_status='paid'")->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch payment data for chart
$paymentQuery = $conn->query("SELECT DATE(payment_date) AS date, SUM(amount) AS total FROM payments WHERE payment_status='paid' GROUP BY DATE(payment_date)");
$dates = [];
$amounts = [];
while ($row = $paymentQuery->fetch(PDO::FETCH_ASSOC)) {
    $dates[] = $row['date'];
    $amounts[] = $row['total'];
}

// Generate PDF
if (isset($_GET['generate_pdf'])) {
    $pdf = new PDF();
    $pdf->AddPage();

    // Add Chart Image if Exists
    $chartPath = '/opt/lampp/htdocs/project/admin/Chart/chart.png';
    if (file_exists($chartPath)) {
        $pdf->Image($chartPath, 10, 40, 190); // Adjust position and size
        $pdf->Ln(60); // Move cursor down
    }

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Tenant Name', 1);
    $pdf->Cell(50, 10, 'Email', 1);
    $pdf->Cell(40, 10, 'Phone', 1);
    $pdf->Cell(50, 10, 'Property Rented', 1);
    $pdf->Ln();

    $query = "SELECT u.full_name, u.email, u.phone, p.name AS property_name FROM tenants t 
              JOIN users u ON t.user_id = u.user_id 
              JOIN properties p ON t.property_id = p.property_id";
    $stmt = $conn->query($query);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 10, $row['full_name'], 1);
        $pdf->Cell(50, 10, $row['email'], 1);
        $pdf->Cell(40, 10, $row['phone'], 1);
        $pdf->Cell(50, 10, $row['property_name'], 1);
        $pdf->Ln();
    }

    $pdf->Output('D', 'report.pdf');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Reports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Rental Management System Reports</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text"> <?php echo $totalUsers; ?> </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Properties</h5>
                        <p class="card-text"> <?php echo $totalProperties; ?> </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Tenants</h5>
                        <p class="card-text"> <?php echo $totalTenants; ?> </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Payments</h5>
                        <p class="card-text">$<?php echo $totalPayments; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <canvas id="paymentsChart"></canvas>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                let dates = <?php echo json_encode(array_values($dates)); ?>;
                let amounts = <?php echo json_encode(array_values($amounts)); ?>;
                
                console.log("Dates:", dates);  // Debugging
                console.log("Amounts:", amounts);  // Debugging
                
                if (!dates.length || !amounts.length) {
                    console.error("Chart data is empty!");
                    return;
                }

                const ctx = document.getElementById('paymentsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Daily Payments ($)',
                            data: amounts,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });

                // Convert chart to image and send to PHP
                setTimeout(() => {
                    const chartImage = document.getElementById('paymentsChart').toDataURL('image/png');
                    fetch('Report/save_chart.php', {
                        method: 'POST',
                        body: JSON.stringify({ image: chartImage }),
                        headers: { 'Content-Type': 'application/json' }
                    }).then(response => response.text()).then(console.log).catch(console.error);
                }, 3000);
            });
        </script>


        <div class="text-center my-4">
            <a href="Report/reports.php?generate_pdf=1" class="btn btn-primary">Download PDF Report</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
