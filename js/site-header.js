document.addEventListener('DOMContentLoaded', function () {
    const burgerBtn = document.getElementById('nav-burger-btn');
    const panel = document.getElementById('mobile-nav-panel');
    const overlay = document.getElementById('mobile-nav-overlay');
    const closeBtn = document.getElementById('mobile-nav-close-btn');
    const linksContainer = document.getElementById('mobile-nav-links');

    if (!burgerBtn || !panel || !overlay || !closeBtn || !linksContainer) return;

    const sourceNav = document.querySelector('.account-nav');
    if (!sourceNav) return;

    const linksToClone = sourceNav.querySelectorAll('a:not(.account-details a)');
    linksToClone.forEach(function (link) {
        linksContainer.appendChild(link.cloneNode(true));
    });

    function openPanel() {
        panel.classList.add('open');
        overlay.classList.add('open');
    }

    function closePanel() {
        panel.classList.remove('open');
        overlay.classList.remove('open');
    }

    function togglePanel() {
        panel.classList.contains('open') ? closePanel() : openPanel();
    }

    burgerBtn.addEventListener('click', togglePanel);
    overlay.addEventListener('click', closePanel);
    closeBtn.addEventListener('click', closePanel);
});
