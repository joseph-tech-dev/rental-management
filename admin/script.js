function loadContent(page) {
    fetch(page)
        .then(response => response.text())
        .then(data => {
            document.getElementById("main-content").innerHTML = data;
        })
        .catch(error => console.error("Error loading content:", error));
}

document.addEventListener("DOMContentLoaded", function () {
    loadContent('dashboard.php'); // Load dashboard by default
});
