x<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Property Images</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="./css/complaints.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <h2>Manage Property Images</h2>
        <button id="addImageBtn" class="btn">+ Add Property Image</button>
        <table>
            <thead>
                <tr>
                    <th>Image ID</th>
                    <th>Property ID</th>
                    <th>Image</th>
                    <th>Uploaded At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="imageTable">
                <!-- Data loaded dynamically -->
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 id="modalTitle">Add Property Image</h3>
            <form id="imageForm" enctype="multipart/form-data">
                <input type="hidden" id="image_id">
                <label>Property ID:</label>
                <input type="number" id="property_id" required>
                <label>Image:</label>
                <input type="file" id="image_url" name="image_url" accept="image/*" required>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function loadImages() {
                $.ajax({
                    url: 'pages/add_get_images.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Response received:", response);
                        let html = "";
                        response.forEach(row => {
                            html += `
                                <tr>
                                    <td>${row.image_id}</td>
                                    <td>${row.property_id}</td>
                                    <td><img src="${row.image_url}" alt="Property Image" width="100"></td>
                                    <td>${row.uploaded_at}</td>
                                    <td>
                                        <button class="editBtn" 
                                            data-id="${row.image_id}" 
                                            data-property="${row.property_id}" 
                                            data-image="${row.image_url}">Edit</button>
                                        <button class="deleteBtn" data-id="${row.image_id}">Delete</button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#imageTable').html(html);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", status, error);
                    }
                });
            }
            loadImages();

            $('#addImageBtn').click(function() {
                $('#imageForm')[0].reset();
                $('#modalTitle').text('Add Property Image');
                $('#imageModal').show();
            });

            $('.close').click(function() {
                $('#imageModal').hide();
            });

            $('#imageForm').submit(function(e) {
                e.preventDefault();
                
                let formData = new FormData(this);
                let propertyId = $('#property_id').val();
                if (!propertyId) {
                    Swal.fire('Error', 'Property ID is required!', 'error');
                    return;
                }
                formData.append("property_id", propertyId);
                
                console.log("Form Data Sent:");
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ": ", pair[1]);
                }

                $.ajax({
                    url: 'pages/add_get_images.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        Swal.fire('Success', response, 'success');
                        $('#imageModal').hide();
                        loadImages();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", status, error);
                    }
                });
            });

            $(document).on('click', '.editBtn', function() {
                let data = $(this).data();
                $('#image_id').val(data.id);
                $('#property_id').val(data.property);
                $('#modalTitle').text('Edit Property Image');
                $('#imageModal').show();
            });

            $(document).on('click', '.deleteBtn', function() {
                let imageId = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('pages/add_get_images.php', { image_id: imageId }, function(response) {
                            Swal.fire('Deleted!', response, 'success');
                            loadImages();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
