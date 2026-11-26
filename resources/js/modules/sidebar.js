export function initSidebar() {
    const sidebar = document.getElementById("sidebar");
    const sidebarToggle = document.getElementById("sidebarToggle");

    if (!sidebar || !sidebarToggle) {
        return;
    }

    sidebarToggle.addEventListener("click", function () {
        sidebar.classList.toggle("show");
    });
}
