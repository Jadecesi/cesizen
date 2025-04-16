document.addEventListener("DOMContentLoaded", initRedirection);
document.addEventListener("modalContentLoaded", initRedirection);

function initRedirection() {
    document.addEventListener("click", function (e) {
        const target = e.target.closest('[data-redirect][data-target="modal"]');
        if (!target) return;

        e.preventDefault();
        const redirectUrl = target.dataset.redirect;
        if (!redirectUrl) return;

        // Ferme le modal courant
        if (window.customModal) {
            window.customModal.close();
            setTimeout(() => {
                window.customModal.loadContentInModal(redirectUrl);
            }, 300);
        }
    });
}