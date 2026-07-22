import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.directive('reveal', (el, { modifiers }, { cleanup }) => {
    const delay = modifiers.includes('delay') ? parseInt(modifiers[modifiers.indexOf('delay') + 1] || '0', 10) : 0;

    el.classList.add('reveal-hidden');

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    if (delay > 0) {
                        el.style.animationDelay = `${delay}ms`;
                    }
                    el.classList.remove('reveal-hidden');
                    el.classList.add('reveal-visible');
                    observer.unobserve(el);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );

    observer.observe(el);

    cleanup(() => observer.disconnect());
});

Alpine.start();
