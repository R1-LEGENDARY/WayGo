document.addEventListener('DOMContentLoaded', function () {
    const toast = document.querySelector('.toast');
    if (!toast) return;

    const closeButton = toast.querySelector('.toast-close');
    let dismissTimeout;

    function dismissToast() {
        clearTimeout(dismissTimeout);
        toast.classList.add('toast-hidden');
    }

    if (closeButton) {
        closeButton.addEventListener('click', dismissToast);
    }

    dismissTimeout = setTimeout(dismissToast, 3000);
});
