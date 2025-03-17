<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management - Room Selection</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">Solar Plaza Property Management System</div>
                <nav>
                    <ul>
                        <li><a href="index.php" class="active">Home</a></li>
                        <li><a href="./property/property.php">Properties</a></li>
                        <li><a href="./tenants/tenant.php">Tenants</a></li>
                        <li><a href="./auth/login.php">Login</a></li>
                        <li><a href="./auth/reports.php">Report</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    
    <section class="hero">
        <div class="container">
            <h1>Find Your Perfect Room</h1>
            <p>Browse our available properties and select a room that suits your needs.</p>
        </div>
    </section>
    
    <main class="container">
        <section class="property-grid" id="property-container">

            <!-- Properties will be loaded here dynamically by JavaScript -->
        </section>
    </main>
    
    <script src="index.js"></script>
</body>
</html>
