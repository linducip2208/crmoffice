<div x-data="{
        choice: localStorage.getItem('cookie-consent'),
        acceptAll() {
            localStorage.setItem('cookie-consent', 'all');
            this.choice = 'all';
            document.cookie = 'cookie-analytics=1; max-age=' + (60*60*24*365) + '; path=/; SameSite=Lax';
        },
        acceptEssential() {
            localStorage.setItem('cookie-consent', 'essential');
            this.choice = 'essential';
        }
    }"
    x-show="!choice"
    x-cloak
    class="fixed bottom-0 left-0 right-0 z-[9999] bg-white/95 backdrop-blur-md border-t border-stone-200 shadow-[0_-8px_30px_-12px_rgba(28,25,23,.12)]"
    x-transition:enter="transition duration-500"
    x-transition:enter-start="translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100">
    <div class="max-w-7xl mx-auto px-5 md:px-8 py-4 flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div class="flex-1 text-sm text-stone-600 leading-relaxed">
            Situs ini menggunakan cookie untuk analitik dan peningkatan pengalaman. Dengan melanjutkan, Anda menyetujui penggunaan cookie.
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
            <button @click="acceptEssential()"
                class="px-4 py-2 text-sm font-medium text-stone-700 border border-stone-300 rounded-lg hover:bg-stone-50 transition">
                Hanya Essential
            </button>
            <button @click="acceptAll()"
                class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-br from-emerald-600 to-teal-600 rounded-lg shadow-md shadow-emerald-600/25 hover:brightness-105 hover:-translate-y-px transition">
                Terima Semua
            </button>
        </div>
    </div>
</div>
