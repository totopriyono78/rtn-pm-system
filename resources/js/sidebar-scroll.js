// Posisi scroll menu sidebar -- dipertahankan lintas navigasi halaman.
// Setiap klik menu di sidebar adalah <a href> biasa (full page reload, bukan
// SPA/wire:navigate), jadi browser secara default selalu me-reset scroll sidebar
// ke atas tiap pindah halaman walau sidebar-nya sendiri tetap terlihat terus
// (lihat perbaikan sticky sidebar di layouts/app.blade.php). Vanilla JS murni,
// tanpa Alpine/eval -- konsisten dengan hero-carousel.js/mobile-menu.js.
//
// Disimpan di sessionStorage (bukan localStorage): bertahan lintas reload halaman
// dalam tab/sesi yang sama, tapi reset wajar begitu tab/browser ditutup -- tidak
// perlu dibersihkan manual, dan tidak "nyangkut" ke sesi login berikutnya.
const STORAGE_KEY = 'rtn_sidebar_scroll_top';

export function initSidebarScroll() {
    // Sengaja target nav DI DALAM #sidebar-desktop secara spesifik -- partial
    // sidebar-nav.blade.php di-include dua kali (desktop + drawer mobile), jadi
    // query tanpa scoping ini akan ambil nav mobile yang salah (atau yang
    // ditemukan lebih dulu di DOM, tidak pasti mana).
    const nav = document.querySelector('#sidebar-desktop nav');
    if (!nav) return;

    const saved = sessionStorage.getItem(STORAGE_KEY);
    if (saved !== null) {
        nav.scrollTop = parseInt(saved, 10) || 0;
    }

    nav.addEventListener('scroll', () => {
        sessionStorage.setItem(STORAGE_KEY, String(nav.scrollTop));
    }, { passive: true });

    // Safety net: simpan juga tepat saat link menu diklik (sebelum browser
    // memuat halaman baru), supaya posisi tersimpan walau user klik tanpa
    // sempat memicu event scroll lagi setelah scroll terakhirnya.
    nav.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            sessionStorage.setItem(STORAGE_KEY, String(nav.scrollTop));
        }
    });
}
