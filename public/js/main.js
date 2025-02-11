document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.open-modal').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            var modal = document.getElementById('dynamicModal');
            var modalTitle = modal ? modal.querySelector('#modalTitle') : null;
            var modalBody = modal ? modal.querySelector('#modalBody') : null;
            var modalFlash = modal ? modal.querySelector('#modalFlash') : null;
            var url = button.getAttribute('href');

            if (modal && modalBody && url) {
                if (modalTitle) {
                    modalTitle.textContent = button.textContent || 'Chargement...';
                }

                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        var tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;

                        var newContent = tempDiv.querySelector('.modal-body')?.innerHTML;
                        var newFlash = tempDiv.querySelector('.modal-message-flash')?.innerHTML;
                        var newForm = tempDiv.querySelector('#signupForm');

                        if (newContent) {
                            modalBody.innerHTML = newContent;
                        } else {
                            modalBody.innerHTML = "<p>Une erreur est survenue.</p>";
                        }

                        if (modalFlash && newFlash) {
                            modalFlash.innerHTML = newFlash;
                        }

                        modal.style.display = 'flex';

                        // 🔥 Intercepter la soumission du formulaire
                        if (newForm) {
                            newForm.addEventListener('submit', function(event) {
                                event.preventDefault();

                                let formData = new FormData(newForm);

                                fetch(newForm.action, {
                                    method: 'POST',
                                    body: formData,
                                })
                                    .then(response => response.text())
                                    .then(html => {
                                        var tempDiv = document.createElement('div');
                                        tempDiv.innerHTML = html;

                                        var updatedContent = tempDiv.querySelector('.modal-body')?.innerHTML;
                                        var updatedFlash = tempDiv.querySelector('.modal-message-flash')?.innerHTML;

                                        if (updatedContent) {
                                            modalBody.innerHTML = updatedContent;
                                        }

                                        if (modalFlash && updatedFlash) {
                                            modalFlash.innerHTML = updatedFlash;
                                        }

                                        console.log('Form submitted via AJAX');
                                    })
                                    .catch(error => console.error('Erreur lors de la soumission AJAX:', error));
                            });
                        }
                    })
                    .catch(error => {
                        modalBody.innerHTML = "<p>Une erreur est survenue.</p>";
                        console.error('Erreur lors du chargement de la modal:', error);
                    });
            }
        });
    });
});
