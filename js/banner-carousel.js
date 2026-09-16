document.addEventListener('DOMContentLoaded', function () {
    const carousel = document.getElementById('banner-carousel');
    if (!carousel) return;

    const track = document.getElementById('banner-track');
    const dots = document.querySelectorAll('#banner-dots .banner-dot');
    const prevBtn = document.getElementById('banner-prev');
    const nextBtn = document.getElementById('banner-next');

    if (!track || !dots.length) return;

    const slideCount = dots.length;
    let currentIndex = 0;
    let autoSlideInterval = null;

    function goToSlide(index) {
        currentIndex = (index + slideCount) % slideCount;
        track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';
        dots.forEach(function (dot, i) {
            dot.classList.toggle('active', i === currentIndex);
        });
    }

    function startAutoSlide() {
        clearInterval(autoSlideInterval);
        autoSlideInterval = setInterval(function () {
            goToSlide(currentIndex + 1);
        }, 2000);
    }

    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
        autoSlideInterval = null;
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            goToSlide(currentIndex - 1);
            stopAutoSlide();
            startAutoSlide();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            goToSlide(currentIndex + 1);
            stopAutoSlide();
            startAutoSlide();
        });
    }

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goToSlide(parseInt(this.dataset.index, 10));
            stopAutoSlide();
            startAutoSlide();
        });
    });

    let touchStartX = 0;

    track.addEventListener('touchstart', function (event) {
        touchStartX = event.touches[0].clientX;
        stopAutoSlide();
    });

    track.addEventListener('touchend', function (event) {
        const touchEndX = event.changedTouches[0].clientX;
        const diff = touchStartX - touchEndX;

        if (Math.abs(diff) > 40) {
            goToSlide(diff > 0 ? currentIndex + 1 : currentIndex - 1);
        }
        startAutoSlide();
    });

    carousel.addEventListener('mouseenter', stopAutoSlide);
    carousel.addEventListener('mouseleave', startAutoSlide);

    startAutoSlide();
});
