import { initHeroCarousel } from './hero-carousel.js';
import { initMobileMenu } from './mobile-menu.js';

// Halaman Company Website (publik) adalah Blade biasa, bukan SPA -- cukup jalankan
// sekali saat DOM siap. Kedua fungsi ini no-op (langsung return) kalau elemen yang
// dicari tidak ada di halaman, jadi aman dipanggil di semua halaman publik.
document.addEventListener('DOMContentLoaded', () => {
    initHeroCarousel();
    initMobileMenu();
});
