<div x-data="meetingNotesHandler" class="space-y-5">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Meeting Mentah</label>
        <textarea
            x-model="rawNotes"
            rows="6"
            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            placeholder="Paste raw meeting notes / transcript di sini..."
        ></textarea>
    </div>

    <div class="flex items-center gap-3">
        <button
            type="button"
            @click="generate"
            :disabled="loading || !rawNotes.trim()"
            x-html="loading ? spinner : '✨ Generate dengan AI'"
            class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition disabled:opacity-50"
            :class="loading ? 'bg-gray-400 cursor-wait' : 'bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700'"
        ></button>
        <span x-show="error" x-text="error" class="text-sm text-danger-600"></span>
    </div>

    <template x-if="results">
        <div class="space-y-4 border-t pt-4">
            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                <h4 class="text-sm font-semibold text-indigo-900 mb-2">Ringkasan Meeting</h4>
                <p x-text="results.summary" class="text-sm text-indigo-800 leading-relaxed"></p>
            </div>

            <template x-if="results.action_items && results.action_items.length">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Action Items</h4>
                    <div class="space-y-2">
                        <template x-for="(item, i) in results.action_items" :key="i">
                            <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 hover:border-gray-300 transition cursor-pointer">
                                <input
                                    type="checkbox"
                                    x-model="selectedActionItems[i]"
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                >
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900" x-text="item.task"></div>
                                    <div class="flex flex-wrap gap-2 mt-1 text-xs text-gray-500">
                                        <span x-show="item.assignee_hint" class="inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span x-text="item.assignee_hint"></span>
                                        </span>
                                        <span x-show="item.deadline_hint" class="inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span x-text="item.deadline_hint"></span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-red-100 text-red-700': item.priority === 'high',
                                                'bg-yellow-100 text-yellow-700': item.priority === 'medium',
                                                'bg-green-100 text-green-700': item.priority === 'low'
                                            }"
                                        >
                                            <span x-text="item.priority"></span>
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </template>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Centang action items yang ingin dibuat sebagai Task.</p>
                </div>
            </template>

            <template x-if="results.decisions && results.decisions.length">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Keputusan</h4>
                    <ul class="space-y-1">
                        <template x-for="(d, i) in results.decisions" :key="i">
                            <li class="flex items-start gap-2 text-sm text-gray-700">
                                <span class="text-indigo-500 mt-0.5">&#x2022;</span>
                                <span x-text="d"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>

            <template x-if="results.attendees_mentioned && results.attendees_mentioned.length">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-1">Peserta Tercatat</h4>
                    <div class="flex flex-wrap gap-1">
                        <template x-for="(name, i) in results.attendees_mentioned" :key="i">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700" x-text="name"></span>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="results.next_meeting">
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                    <span class="text-xs font-medium text-blue-700">Meeting berikutnya:</span>
                    <span class="text-sm text-blue-800 ml-1" x-text="results.next_meeting"></span>
                </div>
            </template>

            <div class="flex items-center gap-3 pt-2">
                <button
                    type="button"
                    @click="save"
                    :disabled="saving"
                    x-html="saving ? savingSpinner : 'Simpan Catatan Meeting'"
                    class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 transition disabled:opacity-50"
                ></button>
                <span x-show="saveError" x-text="saveError" class="text-sm text-danger-600"></span>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('meetingNotesHandler', () => ({
            rawNotes: '',
            loading: false,
            saving: false,
            error: '',
            saveError: '',
            results: null,
            selectedActionItems: [],
            spinner: '<svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Processing...',
            savingSpinner: '<svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Menyimpan...',

            generateUrl: '{{ $generateUrl }}',
            relatedType: '{{ $relatedType }}',
            relatedId: '{{ $relatedId }}',

            csrf: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',

            async generate() {
                if (!this.rawNotes.trim()) return;
                this.loading = true;
                this.error = '';
                this.results = null;

                try {
                    const resp = await fetch(this.generateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'generate',
                            raw_text: this.rawNotes,
                            related_type: this.relatedType,
                            related_id: this.relatedId,
                        }),
                    });

                    const data = await resp.json();

                    if (!resp.ok) {
                        this.error = data.error || 'Gagal generate. Pastikan LLM provider sudah dikonfigurasi.';
                        return;
                    }

                    this.results = data;
                    this.selectedActionItems = (data.action_items || []).map(() => true);
                } catch (e) {
                    this.error = 'Gagal terhubung ke server. Coba lagi.';
                } finally {
                    this.loading = false;
                }
            },

            async save() {
                if (!this.results) return;
                this.saving = true;
                this.saveError = '';

                const checkedItems = (this.results.action_items || []).filter((_, i) => this.selectedActionItems[i]);
                const checkedIndices = this.selectedActionItems
                    .map((checked, i) => checked ? i : null)
                    .filter(i => i !== null);

                try {
                    const resp = await fetch(this.generateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'save',
                            raw_text: this.rawNotes,
                            related_type: this.relatedType,
                            related_id: this.relatedId,
                            structured_notes: this.results,
                            selected_action_indices: checkedIndices,
                            create_tasks: checkedItems.length > 0,
                        }),
                    });

                    const data = await resp.json();

                    if (!resp.ok) {
                        this.saveError = data.error || 'Gagal menyimpan.';
                        return;
                    }

                    window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: '{{ $modalId ?? \'\' }}' } }));

                    if (data.notification) {
                        setTimeout(() => {
                            window.dispatchEvent(new CustomEvent('filament-notify', {
                                detail: { type: 'success', title: data.notification.title, body: data.notification.body }
                            }));
                        }, 200);
                    }
                } catch (e) {
                    this.saveError = 'Gagal terhubung ke server.';
                } finally {
                    this.saving = false;
                }
            },
        }));
    });
</script>
