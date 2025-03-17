<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require '/opt/lampp/htdocs/project/config/db.php';

if (!isset($_GET['property_id']) || empty($_GET['property_id'])) {
    die("Invalid Property ID");
}

$property_id = intval($_GET['property_id']);

$sql = "SELECT * FROM properties WHERE property_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    die("Property not found");
}

$sql_images = "SELECT image_url FROM property_images WHERE property_id = ?";
$stmt_images = $conn->prepare($sql_images);
$stmt_images->execute([$property_id]);
$images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

function safe_value($value, $default = "N/A") {
    return isset($value) && $value !== null ? htmlspecialchars($value) : $default;
}

// Include the header at the top
include '/opt/lampp/htdocs/project/includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details</title>

    <!-- Bootstrap & SwiperJS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            background-image: url("../images/detail-background.jpg");
            background-size: cover;  /* Ensure the image covers the entire viewport */
            background-position: center center;  /* Center the image */
            background-repeat: no-repeat;  /* Prevent the image from repeating */
            height: 100vh;  /* Ensure the body takes up the full height of the viewport */
            margin: 0;  /* Remove default margin */

            font-family: 'Poppins', sans-serif;
            color: white;
            background-color: #222;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ensures full height */
        }

        .content {
            flex-grow: 1; /* Allows the content to expand */
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 850px;
            width: 100%;
        }

        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .swiper-container {
            width: 100%;
            height: 350px;
            border-radius: 12px;
            overflow: hidden;
        }

        .swiper-slide img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-radius: 12px;
            transition: transform 0.3s ease;
        }

        .swiper-slide:hover img {
            transform: scale(1.05);
        }

        .swiper-pagination-bullet {
            background: #fff !important;
            opacity: 0.7;
        }

        .swiper-pagination-bullet-active {
            background: #ffcc00 !important;
            opacity: 1;
        }

        .property-title {
            font-size: 26px;
            font-weight: bold;
            text-align: center;
            color: #fff;
            margin-top: 15px;
            text-transform: uppercase;
        }

        .property-details {
            font-size: 18px;
            margin-top: 15px;
            line-height: 1.6;
        }

        .property-details i {
            color: #ffcc00;
            margin-right: 8px;
        }

        .price-tag {
            background: #ffcc00;
            color: #222;
            padding: 8px 15px;
            font-weight: bold;
            border-radius: 20px;
            display: inline-block;
            font-size: 20px;
        }

        footer {
            background: #111;
            color: white;
            text-align: center;
            padding: 10px 0;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="content">
    <div class="container">
        <div class="card">
            
            <!-- Image Carousel -->
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <?php if ($images): ?>
                        <?php foreach ($images as $image): ?>
                            <div class="swiper-slide">
                                <img src="http://localhost/project/admin/<?php echo safe_value($image['image_url']); ?>" alt="Property Image">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="swiper-slide">
                            <img src="https://via.placeholder.com/800x400?text=No+Image+Available" alt="No Image">
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Pagination & Navigation -->
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

            <!-- Property Details -->
            <h2 class="property-title"><?php echo safe_value($property['name']); ?></h2>
            <div class="text-center mt-2">
                <span class="price-tag">$<?php echo safe_value(number_format($property['rent_amount'], 2)); ?></span>
            </div>
            <div class="property-details mt-4">
                <p><i class="fas fa-map-marker-alt"></i><strong> Address:</strong> <?php echo safe_value($property['address']); ?></p>
                <p><i class="fas fa-home"></i><strong> Type:</strong> <?php echo safe_value($property['type']); ?></p>
                <p><i class="fas fa-info-circle"></i><strong> Status:</strong> <?php echo safe_value($property['status']); ?></p>
                <p><i class="fas fa-ruler-combined"></i><strong> Size:</strong> <?php echo safe_value($property['size']); ?> sqft</p>
            </div>
            
        </div>
    </div>
</div>

<!-- JS for Bootstrap & Swiper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
<script>
    var swiper = new Swiper('.swiper-container', {
        loop: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        effect: 'fade',
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        }
    });
</script>

</body>
<!-- Include the footer at the bottom -->
<?php include '/opt/lampp/htdocs/project/includes/footer.php'; ?>
</html>
