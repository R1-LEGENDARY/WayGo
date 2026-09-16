document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.banner-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm('Delete this banner?')) {
                event.preventDefault();
            }
        });
    });
});
