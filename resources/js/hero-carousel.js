// Carousel Beranda (Company Website) -- vanilla JS murni, TANPA Alpine.js/eval.
// Alasan: direktif Alpine (x-data, x-show, dst) dievaluasi lewat `new Function()` di
// browser, yang diblokir oleh sebagian antivirus/Content-Security-Policy (mis.
// Kaspersky Web Anti-Virus yang menyisipkan CSP ketat ke semua traffic HTTP,
// termasuk localhost). Dengan vanilla JS biasa (dikompilasi Vite, bukan di-eval saat
// runtime), carousel ini tetap jalan untuk SEMUA pengunjung apa pun pengaturan
// keamanan di komputer mereka. Markup slide dirender penuh di server (Blade), file
// ini hanya mengatur perilaku interaktifnya (auto-geser, tombol, dot, drag/swipe).
export function initHeroCarousel() {
    const root = document.getElementById('hero-carousel');
    if (!root) return;

    const track = document.getElementById('hero-carousel-track');
    const slides = Array.from(track.querySelectorAll('.hero-slide'));
    const dots = Array.from(root.querySelectorAll('.hero-carousel-dot'));
    const prevBtn = document.getElementById('hero-carousel-prev');
    const nextBtn = document.getElementById('hero-carousel-next');
    if (slides.length === 0) return;

    let active = 0;
    let timer = null;
    const intervalMs = parseInt(root.dataset.autoplay || '6000', 10);

    function render() {
        slides.forEach((el, i) => {
            el.classList.remove('z-10', 'opacity-100', 'translate-x-0', 'opacity-0', 'translate-x-12', '-translate-x-12', 'pointer-events-none');
            if (i === active) {
                el.classList.add('z-10', 'opacity-100', 'translate-x-0');
            } else if (i < active) {
                el.classList.add('opacity-0', '-translate-x-12', 'pointer-events-none');
            } else {
                el.classList.add('opacity-0', 'translate-x-12', 'pointer-events-none');
            }
        });
        dots.forEach((d, i) => {
            d.classList.remove('w-6', 'bg-white', 'w-2', 'bg-white/40', 'hover:bg-white/60');
            if (i === active) {
                d.classList.add('w-6', 'bg-white');
            } else {
                d.classList.add('w-2', 'bg-white/40', 'hover:bg-white/60');
            }
        });
    }

    function goTo(i) {
        active = ((i % slides.length) + slides.length) % slides.length;
        render();
    }
    function next() { goTo(active + 1); }
    function prev() { goTo(active - 1); }

    function stop() { if (timer) { clearInterval(timer); timer = null; } }
    function start() { stop(); if (slides.length > 1) { timer = setInterval(next, intervalMs); } }
    function restart() { stop(); start(); }

    if (prevBtn) prevBtn.addEventListener('click', () => { prev(); restart(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { next(); restart(); });
    dots.forEach((d, i) => d.addEventListener('click', () => { goTo(i); restart(); }));

    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);

    let dragging = false;
    let startX = 0;
    let deltaX = 0;
    function dragStart(x) { dragging = true; startX = x; deltaX = 0; stop(); }
    function dragMove(x) { if (!dragging) return; deltaX = x - startX; }
    function dragEnd() {
        if (!dragging) return;
        if (deltaX < -50) next();
        else if (deltaX > 50) prev();
        dragging = false;
        deltaX = 0;
        start();
    }
    track.addEventListener('mousedown', (e) => dragStart(e.clientX));
    track.addEventListener('mousemove', (e) => dragMove(e.clientX));
    window.addEventListener('mouseup', dragEnd);
    track.addEventListener('touchstart', (e) => dragStart(e.touches[0].clientX), { passive: true });
    track.addEventListener('touchmove', (e) => dragMove(e.touches[0].clientX), { passive: true });
    track.addEventListener('touchend', dragEnd);

    render();
    start();
}
