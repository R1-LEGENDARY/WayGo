document.addEventListener('DOMContentLoaded', function () {
    initNoChangesGuard('edit-vehicle-form', 'index.php');

    const gallery = document.getElementById('admin-gallery');
    if (!gallery || !gallery.dataset.vehicleId) return;
    const vehicleId = gallery.dataset.vehicleId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let draggedItem = null;

    gallery.querySelectorAll('.admin-gallery-item').forEach(function (item) {
        item.addEventListener('dragstart', function () {
            draggedItem = item;
            item.classList.add('dragging');
        });

        item.addEventListener('dragend', function () {
            item.classList.remove('dragging');
            saveNewOrder();
        });

        item.addEventListener('dragover', function (event) {
            event.preventDefault();
            const afterElement = getDragAfterElement(gallery, event.clientX);
            if (!afterElement) {
                gallery.appendChild(draggedItem);
            } else {
                gallery.insertBefore(draggedItem, afterElement);
            }
        });
    });

    function getDragAfterElement(container, x) {
        const items = [...container.querySelectorAll('.admin-gallery-item:not(.dragging)')];
        return items.reduce(function (closest, child) {
            const box = child.getBoundingClientRect();
            const offset = x - box.left - box.width / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            }
            return closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function saveNewOrder() {
        const orderedIds = [...gallery.querySelectorAll('.admin-gallery-item')].map(function (item) {
            return parseInt(item.dataset.imageId, 10);
        });

        fetch('reorder_images_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ vehicle_id: vehicleId, ordered_ids: orderedIds })
        }).then(function () {
            location.reload();
        });
    }

    const previewModal = document.getElementById('image-preview-modal');
    const previewImg = document.getElementById('preview-modal-img');
    const closePreviewBtn = document.getElementById('close-preview-btn');

    gallery.querySelectorAll('.admin-gallery-preview-trigger').forEach(function (img) {
        img.addEventListener('click', function () {
            previewImg.src = this.dataset.full;
            previewModal.showModal();
        });
    });

    if (closePreviewBtn) {
        closePreviewBtn.addEventListener('click', function () {
            previewModal.close();
        });
    }

    gallery.querySelectorAll('.admin-gallery-delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Delete this photo?')) return;

            const formData = new FormData();
            formData.append('image_id', this.dataset.imageId);
            formData.append('vehicle_id', this.dataset.vehicleId);

            fetch('delete_image_process.php', {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: formData
            }).then(function () {
                location.reload();
            });
        });
    });
});
