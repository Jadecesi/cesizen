document.addEventListener('DOMContentLoaded', function() {
    // Initialiser une seule fois la modal globale
    if (!window.customModal) {
        window.customModal = new CustomModal();
    }
    document.querySelectorAll('.open-modal').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();

            const url = button.getAttribute('href');
            if (url) {
                await window.customModal.loadContentInModal(url);
            } else {
                window.customModal.showModal();
            }
        });
    });
});
