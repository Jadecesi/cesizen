class CustomModal {
    constructor() {
        this.overlay = this.getOverlay();
        this.container = this.getContainer();
        this.init();
    }

    init() {
        document.querySelectorAll('a[data-target="modal"]').forEach(link =>
            link.addEventListener('click', this.handleClick) // ✅ `this` est déjà lié grâce à la fonction fléchée
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
                <div class="modal--script-container"></div> <!-- Ajout d’un conteneur pour les scripts -->
            </div>
        `;
        document.body.appendChild(overlay);
        return overlay;
    }

    getContainer() {
        return this.overlay.querySelector('.modal--content');
    }

    handleClick = async (e) => {
        if (e.defaultPrevented) return;
        e.preventDefault();
        console.log("Valeur de `this` dans handleClick :", this); // 🔍 Vérifier ce que `this` contient
        await this.loadContentInModal(e.target.getAttribute('href'));
    };


    async loadContentInModal(url) {
        this.showModal();
        this.setLoading();

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Erreur réseau');

            const content = await response.text();
            this.setContent(content);
            this.attachEvents();

            // Charge les scripts spécifiques du modal
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;
            this.loadModalScripts(tempDiv.querySelectorAll("script")); // ✅ Correction ici

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
        const newScripts = tempDiv.querySelectorAll("script");

        // Mettre à jour le contenu du modal
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

        // Charger les scripts spécifiques au modal
        this.loadModalScripts(newScripts);

        // 🚀 Déclencher un événement pour signaler que le contenu du modal est chargé
        document.dispatchEvent(new Event("modalContentLoaded"));
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

            const hasSuccess = result.includes('alert-success');
            const errorModal = result.includes('Contenu introuvable.') || result.includes('exception occurred');
            const otherModal = this.overlay.querySelector('.modalImbriquer');
            const redirectUrl = form.dataset.redirect;

            // 🔍 Vérifier si la réponse contient des erreurs
            if (result.includes('class="form-errors"') || result.includes('alert-danger')) {
                console.warn("Erreur détectée dans le formulaire !");
                this.setContent(result); // Recharge le modal avec le contenu mis à jour
                return;
            }
            console.log(redirectUrl);

            if (hasSuccess) {
                this.setLoading();
                setTimeout(() => {
                    this.close();
                    if (redirectUrl) {
                        window.location.href = redirectUrl || form.action;
                    } else {
                        window.location.reload();
                    }
                }, 2000);
            } else if (result.includes('alert-danger')) {
                this.setContent(result);
            } else if (errorModal) {
                this.setError();
            } else if (otherModal) {
                this.setContent(result);
            }
            else {
                this.setLoading();
                setTimeout(() => {
                    this.close();
                    if (redirectUrl) {
                        window.location.href = redirectUrl || form.action;
                    } else {
                        window.location.reload();
                    }
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

    // Charger et exécuter les scripts spécifiques au modal
    loadModalScripts(scripts) {
        const scriptContainer = this.overlay.querySelector('.modal--script-container');
        scriptContainer.innerHTML = '';

        scripts.forEach(script => {
            const newScript = document.createElement('script');
            if (script.src) {
                newScript.src = script.src;
                newScript.defer = true;
            } else {
                newScript.textContent = script.textContent;
            }
            scriptContainer.appendChild(newScript);
        });

    }
}

// Initialisation globale
document.addEventListener('DOMContentLoaded', () => {
    window.customModal = new CustomModal();
});
