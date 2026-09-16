function initNoChangesGuard(formId, leaveUrl) {
    const form = document.getElementById(formId);
    if (!form) return;

    const modal = document.getElementById('no-changes-modal');
    const continueBtn = document.getElementById('no-changes-continue-btn');
    const leaveBtn = document.getElementById('no-changes-leave-btn');
    const saveModal = document.getElementById('save-vehicle-modal');
    const saveConfirmBtn = document.getElementById('save-vehicle-confirm-btn');
    const saveCancelBtn = document.getElementById('save-vehicle-cancel-btn');

    if (!modal || !continueBtn || !leaveBtn) return;

    const initialData = new FormData(form);
    const initialSnapshot = JSON.stringify([...initialData.entries()]);
    let isSaveConfirmed = false;

    form.addEventListener('submit', function (event) {
        const currentData = new FormData(form);
        const currentSnapshot = JSON.stringify([...currentData.entries()]);

        if (isSaveConfirmed) {
            isSaveConfirmed = false;
            return;
        }

        if (currentSnapshot === initialSnapshot) {
            event.preventDefault();
            modal.showModal();
            return;
        }

        event.preventDefault();
        if (saveModal) {
            saveModal.showModal();
        } else {
            form.submit();
        }
    });

    continueBtn.addEventListener('click', function () {
        modal.close();
    });

    leaveBtn.addEventListener('click', function () {
        window.location.href = leaveUrl;
    });

    if (saveConfirmBtn && saveCancelBtn && saveModal) {
        saveConfirmBtn.addEventListener('click', function () {
            saveModal.close();
            isSaveConfirmed = true;
            form.submit();
        });

        saveCancelBtn.addEventListener('click', function () {
            form.reset();
            saveModal.close();
        });

        saveModal.addEventListener('cancel', function () {
            form.reset();
        });
    }
}
