import Alpine from 'alpinejs';

// Alpine untuk landing/marketing: cookie consent, menu mobile, dsb.
// Dipisah dari app.js (entry Inertia/Vue) supaya halaman publik tidak
// memuat bundle aplikasi admin.
window.Alpine = Alpine;

Alpine.start();
