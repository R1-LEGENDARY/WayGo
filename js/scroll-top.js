(function () {
    const btn = document.getElementById('scroll-top-btn');
    if (!btn) return;

    const showAfter = 400;

    function toggleButton() {
        if (window.scrollY > showAfter) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    }

    window.addEventListener('scroll', toggleButton, { passive: true });
    toggleButton();

    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
