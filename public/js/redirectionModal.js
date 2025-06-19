// Remove the DOMContentLoaded listener since CustomModal already handles initialization
document.addEventListener("modalContentLoaded", initRedirection, { once: true });

function initRedirection() {
    // Remove existing listeners first
    document.removeEventListener("click", handleRedirectClick);
    // Add new listener
    document.addEventListener("click", handleRedirectClick);
}

function handleRedirectClick(e) {
    const target = e.target.closest('[data-redirect][data-target="modal"]');
    if (!target) return;

    e.preventDefault();
    e.stopPropagation(); // Prevent event bubbling

    const redirectUrl = target.dataset.redirect;
    if (!redirectUrl || !window.customModal) return;

    // Use a flag to prevent multiple executions
    if (target.dataset.processing === 'true') return;
    target.dataset.processing = 'true';

    window.customModal.close();
    setTimeout(() => {
        window.customModal.loadContentInModal(redirectUrl)
            .finally(() => {
                target.dataset.processing = 'false';
            });
    }, 300);
}