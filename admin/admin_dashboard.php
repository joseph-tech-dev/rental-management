<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="./style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div id="content-area">
            <h2>Welcome to the Admin Dashboard</h2>
            <p>Select a section from the sidebar.</p>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $(".menu-link").click(function (e) {
            e.preventDefault();
            let page = $(this).data("page");

            // Remove 'active' class from all menu items
            $(".menu-link").removeClass("active");

            // Add 'active' class to the clicked menu item
            $(this).addClass("active");

            $("#content-area").load(page, function (response, status, xhr) {
                if (status == "error") {
                    $("#content-area").html("<p>Error loading page. Please try again.</p>");
                }
            });
        });
    });
</script>


</body>
</html>
