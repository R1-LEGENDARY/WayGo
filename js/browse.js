document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('live-search');
    const resultsContainer = document.getElementById('vehicle-results');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const burgerBtn = document.getElementById('burger-btn');
    const sidebarCloseBtn = document.getElementById('sidebar-close-btn');
    let searchTimeout = null;
    const loadedPage = resultsContainer ? Number(resultsContainer.dataset.loadedPage || 1) : 1;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function currentFilterParams() {
        return new URLSearchParams(window.location.search);
    }

    function appendLoadMoreResponse(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html;

        const meta = temp.querySelector('#load-more-meta');
        const hasMore = meta && meta.dataset.hasMore === '1';
        const followingPage = meta ? Number(meta.dataset.nextPage) : null;
        const vehicleGrid = document.getElementById('vehicle-grid');

        if (vehicleGrid) {
            temp.querySelectorAll('.vehicle-card').forEach(function (card) {
                vehicleGrid.appendChild(card);
            });
        }

        const btn = document.getElementById('load-more-btn');
        if (!btn) return { hasMore: hasMore, followingPage: followingPage };

        if (hasMore) {
            btn.dataset.nextPage = String(followingPage);
            btn.textContent = 'Load More Vehicles';
            btn.disabled = false;
        } else {
            btn.remove();
        }

        return { hasMore: hasMore, followingPage: followingPage };
    }

    function fetchMoreVehicles(nextPage, keepButtonState) {
        const params = currentFilterParams();
        params.set('page', String(nextPage));

        const btn = document.getElementById('load-more-btn');
        if (keepButtonState && btn) {
            btn.textContent = 'Loading...';
            btn.disabled = true;
        }

        return fetch('load_more_ajax.php?' + params.toString())
            .then(function (response) { return response.text(); })
            .then(appendLoadMoreResponse);
    }

    function restoreLoadedBrowseState() {
        const savedPage = loadedPage;
        if (savedPage <= 1) return;

        let pendingPage = 2;
        function continueRestoring() {
            if (pendingPage > savedPage) {
                const btn = document.getElementById('load-more-btn');
                if (btn) btn.dataset.nextPage = String(savedPage + 1);
                return;
            }

            fetchMoreVehicles(pendingPage, false).then(function () {
                pendingPage += 1;
                continueRestoring();
            });
        }

        continueRestoring();
    }

    if (searchInput && resultsContainer) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const query = this.value;

            searchTimeout = setTimeout(function () {
                const params = currentFilterParams();
                params.set('search', query);
                params.delete('page');

                fetch('search_ajax.php?' + params.toString())
                    .then(function (response) { return response.text(); })
                    .then(function (html) { resultsContainer.innerHTML = html; });
            }, 300);
        });

        resultsContainer.addEventListener('click', function (event) {
            if (event.target.id === 'load-more-btn') {
                const nextPage = Number(event.target.dataset.nextPage || 2);
                fetchMoreVehicles(nextPage, true);
                return;
            }

            const favoriteBtn = event.target.closest('.favorite-btn');
            if (!favoriteBtn) return;

            event.preventDefault();
            fetch('../favorites/toggle_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
                body: JSON.stringify({ vehicle_id: favoriteBtn.dataset.vehicleId })
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.success) {
                        favoriteBtn.classList.toggle('favorited', data.favorited);
                        favoriteBtn.querySelector('svg').setAttribute('fill', data.favorited ? 'currentColor' : 'none');
                    }
                });
        });

        restoreLoadedBrowseState();
    }

    if (sidebar && overlay && burgerBtn && sidebarCloseBtn) {
        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        }

        burgerBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });
        overlay.addEventListener('click', closeSidebar);
        sidebarCloseBtn.addEventListener('click', closeSidebar);
    }

    const navBurgerBtn = document.getElementById('nav-burger-btn-browse');
    if (navBurgerBtn) {
        const navPanel = document.getElementById('mobile-nav-panel-browse');
        const navOverlay = document.getElementById('mobile-nav-overlay-browse');
        const navCloseBtn = document.getElementById('mobile-nav-close-btn-browse');
        const navLinksContainer = document.getElementById('mobile-nav-links-browse');
        const sourceAccountNav = document.querySelector('.account-nav');

        if (!navPanel || !navOverlay || !navCloseBtn || !navLinksContainer || !sourceAccountNav) return;

        sourceAccountNav.querySelectorAll('a.nav-link-desktop-only').forEach(function (link) {
            navLinksContainer.appendChild(link.cloneNode(true));
        });

        function closeNavPanel() {
            navPanel.classList.remove('open');
            navOverlay.classList.remove('open');
        }

        navBurgerBtn.addEventListener('click', function () {
            navPanel.classList.toggle('open');
            navOverlay.classList.toggle('open');
        });
        navOverlay.addEventListener('click', closeNavPanel);
        navCloseBtn.addEventListener('click', closeNavPanel);
    }
});
