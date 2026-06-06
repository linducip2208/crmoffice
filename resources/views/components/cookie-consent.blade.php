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
    class="fixed bottom-0 left-0 right-0 z-[9999] bg-slate-900/95 backdrop-blur-md border-t border-slate-700/50"
    x-transition:enter="transition duration-500"
    x-transition:enter-start="translate-y-full opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100">
    <div class="max-w-7xl mx-auto px-5 md:px-8 py-4 flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div class="flex-1 text-sm text-slate-300 leading-relaxed">
            Situs ini menggunakan cookie untuk analitik dan peningkatan pengalaman. Dengan melanjutkan, Anda menyetujui penggunaan cookie.
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
            <button @click="acceptEssential()"
                class="px-4 py-2 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-800 hover:text-white transition">
                Hanya Essential
            </button>
            <button @click="acceptAll()"
                class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-brand-500 to-brand-700 rounded-lg shadow-lg hover:shadow-xl hover:-translate-y-px transition">
                Terima Semua
            </button>
        </div>
    </div>
</div>
