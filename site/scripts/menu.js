document.addEventListener('DOMContentLoaded', function() {
    const menu = document.querySelector('.menu');
    const observer = new IntersectionObserver(
        ([e]) => menu.classList.toggle('sticky', e.intersectionRatio < 1),
        { threshold: [1] }
    );
    observer.observe(menu);
});