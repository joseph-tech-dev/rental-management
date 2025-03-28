    </div> <!-- End of main container -->

    <footer class="mt-5 py-4 bg-dark text-white">
        <div class="container">
            <div class="row">
                <!-- Company Info -->
                <div class="col-md-4">
                    <h5>Rental Management System</h5>
                    <p>&copy; <?php echo date('Y'); ?> All Rights Reserved.</p>
                    <p>Email: info@rentalms.com</p>
                    <p>Phone: +123 456 7890</p>
                </div>

                <!-- Quick Links -->
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                        <li><a href="properties.php" class="text-white text-decoration-none">Properties</a></li>
                        <li><a href="about.php" class="text-white text-decoration-none">About Us</a></li>
                        <li><a href="contact.php" class="text-white text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div class="col-md-4 text-md-end">
                    <h5>Follow Us</h5>
                    <a href="#" class="text-white me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
                    <p class="mt-2">6166776B</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        $(document).ready(function() {
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $(".alert").alert('close');
            }, 5000);
            
            // Enable tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Enable popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
    </script>
</body>
</html>
