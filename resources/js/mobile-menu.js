// Menu mobile Company Website -- vanilla JS, TANPA Alpine.js/eval (lihat catatan di
// hero-carousel.js untuk alasannya: Alpine butuh eval() yang bisa diblokir CSP).
export function initMobileMenu() {
    const toggle = document.getElementById('mobile-menu-toggle');
    const panel = document.getElementById('mobile-menu-panel');
    const iconOpen = document.getElementById('mobile-menu-icon-open');
    const iconClose = document.getElementById('mobile-menu-icon-close');
    if (!toggle || !panel) return;

    let open = false;

    function render() {
        panel.classList.toggle('hidden', !open);
        if (iconOpen) iconOpen.classList.toggle('hidden', open);
        if (iconClose) iconClose.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', String(open));
    }

    toggle.addEventListener('click', () => {
        open = !open;
        render();
    });

    render();
}
