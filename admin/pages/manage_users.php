<?php

include './users.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="./css/complaints.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container">
    <h2>Manage Users</h2>

    <button id="addUserBtn" class="btn btn-primary">Add New User</button>

    <table class="user-table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['user_id'] ?></td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['phone']) ?></td>
                    <td><?= ucfirst($user['role']) ?></td>
                    <td>
                        <button class="edit-btn" data-id="<?= $user['user_id'] ?>">Edit</button>
                        <button class="delete-btn" data-id="<?= $user['user_id'] ?>">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Add New User</h2>
        <form id="userForm">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="user_id">

            <label>Full Name:</label>
            <input type="text" name="full_name" required>
            
            <label>Email:</label>
            <input type="email" name="email" required>
            
            <label>Phone:</label>
            <input type="text" name="phone" required>
            
            <label>Role:</label>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
            
            <label>Password:</label>
            <input type="password" name="password" required>
            
            <button type="submit">Save</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {
    console.log("Document ready!");

    // Open Add User Modal
    $("#addUserBtn").click(function () {
        console.log("Add User button clicked!");
        $("#userForm")[0].reset();
        $("#userForm [name='action']").val("add");
        $("#userForm [name='user_id']").val("");
        $("#modalTitle").text("Add New User");
        $("#userModal").fadeIn();
    });

    // Open Edit User Modal
    $(document).on("click", ".edit-btn", function () {
        let user_id = $(this).data("id");
        console.log("Edit button clicked for User ID:", user_id);

        $.post("pages/users.php", { action: "fetch", user_id: user_id }, function (data) {
            try {
                let user = JSON.parse(data);
                if (user.error) {
                    alert("Error: " + user.error);
                } else {
                    $("#userForm [name='user_id']").val(user.user_id);
                    $("#userForm [name='full_name']").val(user.full_name);
                    $("#userForm [name='email']").val(user.email);
                    $("#userForm [name='phone']").val(user.phone);
                    $("#userForm [name='role']").val(user.role);
                    $("#userForm [name='action']").val("edit");
                    $("#modalTitle").text("Edit User");
                    $("#userModal").fadeIn();
                }
            } catch (error) {
                console.error("Invalid JSON response:", data);
                alert("An error occurred while fetching user details.");
            }
        })
        .fail(function(xhr, status, error) {
            console.error("AJAX Error:", xhr.responseText);
            alert("Failed to fetch user data. Check the console for details.");
        });
    });

    // Close Modal
    $(".close").click(function () {
        console.log("Closing modal");
        $("#userModal").fadeOut();
    });

    // Handle Add/Edit User Form Submission
    $(document).on("submit", "#userForm", function (e) {
        e.preventDefault();
        console.log("Form submitted");

        $.post("pages/users.php", $(this).serialize(), function (response) {
            console.log("Response:", response);
            alert(response.message);
            location.reload();
        }, "json")
        .fail(function(xhr, status, error) {
            console.error("Error:", xhr.responseText);
        });
    });

    // Delete User
    $(document).on("click", ".delete-btn", function () {
        let userId = $(this).data("id");
        console.log("Delete button clicked for User ID:", userId);

        if (confirm("Are you sure you want to delete this user?")) {
            $.post("pages/users.php", { action: "delete", user_id: userId }, function (response) {
                alert(response.message);
                location.reload();
            }, "json")
            .fail(function(xhr, status, error) {
                console.error("Delete Error:", xhr.responseText);
            });
        }
    });
});

</script>

</body>
</html>
