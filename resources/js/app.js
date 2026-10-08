import { initHeroCarousel } from './hero-carousel.js';
import { initMobileMenu } from './mobile-menu.js';
import { initSidebarScroll } from './sidebar-scroll.js';

// File ini dipakai bersama oleh SEMUA layout (Company Website publik, Internal
// Portal, Client Portal). Setiap fungsi di sini sengaja no-op (langsung return)
// kalau elemen yang dicarinya tidak ada di halaman, jadi aman dipanggil sekali di
// sini untuk semua halaman tanpa cek layout mana yang sedang aktif.
document.addEventListener('DOMContentLoaded', () => {
    initHeroCarousel();
    initMobileMenu();
    initSidebarScroll();
});
