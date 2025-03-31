function toggleDropdown() {
    let dropdown = document.getElementById("dropdownMenu");
    if (dropdown) {
        dropdown.classList.toggle("show");
    }
}

// Ferme le menu si on clique ailleurs
window.onclick = function(event) {
    let dropdown = document.getElementById("dropdownMenu");
    if (dropdown && !event.target.closest('.user-menu')) {
        dropdown.classList.remove("show");
    }
};
