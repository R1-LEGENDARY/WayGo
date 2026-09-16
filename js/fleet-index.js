document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('delete-vehicle-modal');
    const deleteVehicleId = document.getElementById('delete-vehicle-id');
    const deleteButtons = document.querySelectorAll('.fleet-delete-trigger');
    const closeDeleteBtn = document.getElementById('close-delete-vehicle-btn');
    const cancelDeleteBtn = document.getElementById('cancel-delete-vehicle-btn');

    if (!deleteModal || !deleteVehicleId || !closeDeleteBtn || !cancelDeleteBtn) return;

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            deleteVehicleId.value = this.dataset.vehicleId;
            deleteModal.showModal();
        });
    });

    closeDeleteBtn.addEventListener('click', function () {
        deleteModal.close();
    });

    cancelDeleteBtn.addEventListener('click', function () {
        deleteModal.close();
    });
});
