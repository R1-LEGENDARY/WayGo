document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    document.querySelectorAll('.favorite-btn').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();

            const vehicleId = this.dataset.vehicleId;
            const card = this.closest('.vehicle-card');

            fetch('toggle_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
                body: JSON.stringify({ vehicle_id: vehicleId })
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.success && !data.favorited && card) {
                        card.remove();
                    }
                });
        });
    });
});
