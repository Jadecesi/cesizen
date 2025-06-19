function passwordCheck() {
    console.log("ici");
    const strengthBar = document.querySelector('.strength-progress');
    const strengthLabel = document.querySelector('.strength-label');
    const passwordField = document.querySelector('.password-container input[type="password"]');
    const requirementsTitle = document.querySelector('.requirements');

    const requirements = {
        length: password => password.length >= 8,
        uppercase: password => /[A-Z]/.test(password),
        lowercase: password => /[a-z]/.test(password),
        number: password => /[0-9]/.test(password),
        special: password => /[@$!%*?&]/.test(password)
    };

    if (passwordField) {
        console.log(passwordField);
        passwordField.addEventListener('input', function () {
            const password = this.value;
            let validCount = 0;
            let visibleRequirements = 0;

            Object.keys(requirements).forEach(requirement => {
                const element = document.querySelector(`[data-requirement="${requirement}"]`);
                if (element && requirements[requirement](password)) {
                    element.style.display = 'none';
                    validCount++;
                } else if (element) {
                    element.style.display = 'flex';
                    visibleRequirements++;
                }
            });

            // Gestion du titre des exigences
            if (requirementsTitle) {
                requirementsTitle.style.display = visibleRequirements === 0 ? 'none' : 'block';
            }

            if (strengthBar && strengthLabel) {
                strengthBar.classList.remove('weak', 'medium', 'strong');
                strengthLabel.classList.remove('weak', 'medium', 'strong');

                if (validCount <= 2) {
                    strengthBar.classList.add('weak');
                    strengthLabel.classList.add('weak');
                    strengthLabel.querySelector('span').textContent = 'Faible';
                } else if (validCount <= 4) {
                    strengthBar.classList.add('medium');
                    strengthLabel.classList.add('medium');
                    strengthLabel.querySelector('span').textContent = 'Moyen';
                } else {
                    strengthBar.classList.add('strong');
                    strengthLabel.classList.add('strong');
                    strengthLabel.querySelector('span').textContent = 'Fort';
                }
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", passwordCheck);
document.addEventListener("modalContentLoaded", passwordCheck);