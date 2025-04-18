function toggleDropdown(id) {
    let dropdown = document.getElementById(id);
    if (dropdown) {
        dropdown.classList.toggle("show");
    }
}

// Ferme le menu si on clique ailleurs
window.onclick = function(event) {
    let dropdowns = document.querySelectorAll('.dropdown-menu');
    dropdowns.forEach(function(dropdown) {
        if (!event.target.closest('.user-menu')) {
            dropdown.classList.remove("show");
        }
    });
};