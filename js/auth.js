document.addEventListener('DOMContentLoaded', function () {
    const authForm = document.querySelector('.auth-form-box form');

    if (authForm) {
        authForm.addEventListener('input', function (event) {
            const input = event.target;

            if (!(input instanceof HTMLInputElement)) {
                return;
            }

            input.classList.remove('has-error');

            const inputWrapper = input.closest('.password-field-wrapper') || input;
            const inlineError = inputWrapper.nextElementSibling;

            if (inlineError && inlineError.classList.contains('field-inline-error')) {
                inlineError.remove();
            }
        });
    }

    document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (!input) return;

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
    });
});
