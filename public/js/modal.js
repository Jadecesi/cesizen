class CustomModal {
    constructor() {
        this.overlay = this.getOverlay();
        this.container = this.getContainer();
        this.init();
    }

    init() {
        document.querySelectorAll('a[data-target="modal"]').forEach(link =>
            link.addEventListener('click', (e) => this.handleClick(e))
        );
    }

    getOverlay() {
        return document.querySelector('.modal--overlay') || this.createOverlay();
    }

    createOverlay() {
        const overlay = document.createElement('div');
        overlay.classList.add('modal--overlay');
        overlay.innerHTML = `
            <div class="modal--content">
                <button class="modal--close">&times;</button>
                <div class="modal--body">Chargement...</div>
            </div>
        `;
        document.body.appendChild(overlay);
        return overlay;
    }

    getContainer() {
        return this.overlay.querySelector('.modal--content');
    }

    async handleClick(e) {
        if (e.defaultPrevented) return;
        e.preventDefault();
        await this.loadContentInModal(e.target.getAttribute('href'));
    }

    async loadContentInModal(url) {
        this.showModal();
        this.setLoading();

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Erreur réseau');

            const content = await response.text();
            this.setContent(content);
            this.attachEvents();
        } catch (error) {
            this.setError();
            console.error(error);
        }
    }

    setLoading() {
        this.overlay.querySelector('.modal--body').innerHTML =
            '<div class="text-center"><span class="spinner"></span> Chargement...</div>';
    }

    setError() {
        this.overlay.querySelector('.modal--body').innerHTML =
            '<div class="alert alert-danger">Une erreur est survenue.</div>';
    }

    setContent(html) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;

        const newTitle = tempDiv.querySelector('.modal--title')?.innerText || "Chargement...";
        const newBody = tempDiv.querySelector('.content-modal')?.innerHTML || "<p>Contenu introuvable.</p>";
        const newFlash = tempDiv.querySelector('.modal--flash')?.innerHTML || "";

        // Update flash messages
        const flashContainer = this.overlay.querySelector('.modal--flash');
        if (flashContainer) {
            flashContainer.innerHTML = newFlash;
        }
        this.overlay.querySelector('.modal--body').innerHTML = `
        <div class="modal--flash">${flashContainer ? flashContainer.innerHTML : ''}</div>
        <div class="content-modal">${newBody}</div>
        `;

        const modalTitle = this.overlay.querySelector('.modal--title');
        if (modalTitle) modalTitle.innerHTML = newTitle;

        this.attachFormEvents();
    }

    attachEvents() {
        this.overlay.querySelector('.modal--close').addEventListener('click', () => this.close());
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') this.close(); });
    }

    attachFormEvents() {
        const form = this.overlay.querySelector('form');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.handleFormSubmit(form);
            });
        }
    }

    async handleFormSubmit(form) {
        this.setLoading();

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.text();

            console.log(result);
            this.setContent(result);

            // Check if there are success messages
            const hasSuccess = this.overlay.querySelector('.alert-success');
            const errorModal = result.includes('Contenu introuvable.');
            console.log('hasSuccess:', hasSuccess);
            if (hasSuccess) {
                this.setLoading();
                setTimeout(() => {
                    this.close();
                }, 2000);
            } else if (result.includes('alert-danger')) {
                this.setContent(result);
            } else if (errorModal) {
                this.setError();
            } else {
                this.setLoading();
                setTimeout(() => {
                    this.close();
                    window.location.reload();
                }, 2000);
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.setError();
        }
    }

    showModal() {
        this.overlay.style.display = 'block';
        this.overlay.setAttribute('aria-hidden', 'false');
    }

    close() {
        this.overlay.style.display = 'none';
        this.overlay.setAttribute('aria-hidden', 'true');
    }
}

// Initialisation globale
document.addEventListener('DOMContentLoaded', () => {
    window.customModal = new CustomModal();
});
