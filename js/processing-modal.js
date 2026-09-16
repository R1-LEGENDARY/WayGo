let dotsInterval = null;

function showProcessingModal() {
    const modal = document.getElementById('processingModal');
    if (modal) {
        modal.classList.add('active');
    }

    const dialogElements = document.querySelectorAll('dialog[open]');
    dialogElements.forEach(function (dialog) {
        dialog.close();
    });

    const dotsElement = document.querySelector('.processing-dots');
    if (dotsElement) {
        let dotCount = 1;
        dotsInterval = setInterval(function () {
            dotsElement.textContent = '.'.repeat(dotCount);
            dotCount = dotCount === 3 ? 1 : dotCount + 1;
        }, 500);
    }
}

function hideProcessingModal() {
    const modal = document.getElementById('processingModal');
    if (modal) {
        modal.classList.remove('active');
    }

    if (dotsInterval) {
        clearInterval(dotsInterval);
        dotsInterval = null;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('form');

    forms.forEach(function (form) {
        if (form.method.toUpperCase() !== 'POST') {
            return;
        }

        form.addEventListener('submit', function (event) {
            setTimeout(function () {
                if (event.defaultPrevented) {
                    return;
                }

                showProcessingModal();
                setTimeout(hideProcessingModal, 30000);
            }, 0);
        });
    });
});
