document.addEventListener('DOMContentLoaded', function() {
    function attachFormSubmitEvent(form) {
        if (!form) {
            console.error("❌ Aucun formulaire trouvé pour attacher l'événement submit.");
            return;
        }

    //     form.addEventListener('submit', function(event) {
    //         alert("🔥 Interception du submit !");
    //         event.preventDefault();
    //
    //         let submitButton = form.querySelector('[type="submit"]');
    //         let formData = new FormData(form);
    //
    //         if (submitButton) {
    //             submitButton.disabled = true;
    //             submitButton.textContent = 'Envoi en cours...';
    //         }
    //
    //         fetch(form.action, {
    //             method: 'POST',
    //             body: formData,
    //         })
    //             .then(response => response.text())
    //             .then(html => {
    //                 var tempDiv = document.createElement('div');
    //                 tempDiv.innerHTML = html;
    //
    //                 var updatedContent = tempDiv.querySelector('.modal-body')?.innerHTML;
    //                 var updatedFlash = tempDiv.querySelector('.modal-message-flash')?.innerHTML;
    //
    //                 document.querySelector('#modalBody').innerHTML = updatedContent || document.querySelector('#modalBody').innerHTML;
    //                 document.querySelector('#modalFlash').innerHTML = updatedFlash || "";
    //
    //                 if (submitButton) {
    //                     submitButton.disabled = false;
    //                     submitButton.textContent = 'Valider';
    //                 }
    //
    //             })
    //             .catch(error => {
    //                 console.error('❌ Erreur AJAX:', error);
    //                 if (submitButton) {
    //                     submitButton.disabled = false;
    //                     submitButton.textContent = 'Valider';
    //                 }
    //             });
    //     });
    }

    document.querySelectorAll('.open-modal').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            var modal = document.getElementById('dynamicModal');
            var modalTitle = modal.querySelector('#modalTitle');
            var modalBody = modal.querySelector('#modalBody');
            var modalFlash = modal.querySelector('#modalFlash');
            var url = button.getAttribute('href');
            var closeButton = modal.querySelector('.close-modal');

            if (modal && modalBody && url) {
                modalTitle.textContent = button.textContent || 'Chargement...';

                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        var tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;

                        var newContent = tempDiv.querySelector('.modal-body')?.innerHTML;
                        var newFlash = tempDiv.querySelector('.modal-message-flash')?.innerHTML;
                        var newForm = tempDiv.querySelector('form');

                        modalBody.innerHTML = newContent || "<p>Une erreur est survenue.</p>";
                        if (modalFlash) modalFlash.innerHTML = newFlash || "";

                        modal.style.display = 'flex';

                        if (newForm) {
                            console.log("newForm:", newForm);
                            attachFormSubmitEvent(newForm);
                        } else {
                            console.error('Aucun formulaire trouvé dans la modal.');
                        }


                        if (closeButton) {
                            closeButton.addEventListener('click', function() {
                                modal.style.display = 'none';
                            });
                        }

                    })
                    .catch(error => {
                        modalBody.innerHTML = "<p>Erreur lors du chargement de la modal.</p>";
                        console.error('Erreur:', error);
                    });
            }
        });
    });
});