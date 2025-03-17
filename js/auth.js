document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");
    const signupForm = document.getElementById("signupForm");

    if (loginForm) {
        loginForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            const formData = new FormData(loginForm);

            try {
                const response = await fetch("login.php", {
                    method: "POST",
                    body: formData,
                });

                const text = await response.text();
                if (response.ok) {
                    window.location.href = "dashboard.php"; // Redirect on success
                } else {
                    showError(loginForm, text || "Login failed. Please try again.");
                }
            } catch (error) {
                showError(loginForm, "An error occurred. Please try again.");
            }
        });
    }

    if (signupForm) {
        signupForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            const formData = new FormData(signupForm);

            try {
                const response = await fetch("register.php", {
                    method: "POST",
                    body: formData,
                });

                const text = await response.text();
                if (response.ok) {
                    window.location.href = "login.php?success=1"; // Redirect to login on success
                } else {
                    showError(signupForm, text || "Signup failed. Please try again.");
                }
            } catch (error) {
                showError(signupForm, "An error occurred. Please try again.");
            }
        });
    }

    function showError(form, message) {
        let errorElement = form.querySelector(".error-message");
        if (!errorElement) {
            errorElement = document.createElement("p");
            errorElement.className = "error-message";
            errorElement.style.color = "red";
            form.prepend(errorElement);
        }
        errorElement.textContent = message;
    }
});
