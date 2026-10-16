@auth
@once
<style>
    [x-cloak] { display: none !important; }
    .ai-chat-assistant { position: fixed; bottom: 24px; right: 24px; z-index: 9998; font-family: var(--font-sans, 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif); }
    .ai-chat-fab {
        width: 52px; height: 52px; border-radius: 999px;
        background: linear-gradient(135deg, #10b981 0%, #0d9488 100%);
        color: #fff; border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 1px 2px rgba(13, 148, 136, 0.25), 0 10px 28px -6px rgba(16, 185, 129, 0.45);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .ai-chat-fab:hover { transform: translateY(-2px); box-shadow: 0 2px 6px rgba(13, 148, 136, 0.3), 0 16px 36px -8px rgba(16, 185, 129, 0.55); }
    .ai-chat-fab:active { transform: translateY(0) scale(0.97); }
    .ai-chat-fab:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.35), 0 10px 28px -6px rgba(16, 185, 129, 0.45); }
    .ai-chat-panel {
        position: fixed; bottom: 88px; right: 24px;
        width: 400px; max-width: calc(100vw - 32px);
        max-height: min(560px, calc(100dvh - 120px)); height: 500px;
        background: #fff; border-radius: 16px;
        border: 1px solid rgba(28, 25, 23, 0.08);
        box-shadow: 0 1px 2px rgba(28, 25, 23, 0.04), 0 24px 48px -12px rgba(28, 25, 23, 0.18);
        display: flex; flex-direction: column; overflow: hidden;
    }
    .ai-chat-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 14px;
        background: #ffffff;
        border-bottom: 1px solid rgba(28, 25, 23, 0.07);
        color: #1c1917; flex-shrink: 0;
    }
    .ai-chat-header-chip {
        width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
        background: linear-gradient(135deg, #10b981 0%, #0d9488 100%);
        color: #fff; display: flex; align-items: center; justify-content: center;
    }
    .ai-chat-close { color: #78716c; background: none; border: none; cursor: pointer; border-radius: 8px; padding: 6px; transition: background 0.15s, color 0.15s; display: flex; }
    .ai-chat-close:hover { background: #f5f5f4; color: #1c1917; }
    .ai-chat-messages { flex: 1; overflow-y: auto; padding: 12px; background: #fafaf9; display: flex; flex-direction: column; gap: 8px; }
    .ai-chat-msg-user { display: flex; justify-content: flex-end; }
    .ai-chat-msg-ai { display: flex; justify-content: flex-start; }
    .ai-chat-bubble { max-width: 85%; padding: 10px 14px; border-radius: 14px; font-size: 13px; line-height: 1.55; word-break: break-word; }
    .ai-chat-bubble-user { background: linear-gradient(135deg, #10b981 0%, #0d9488 100%); color: #fff; border-bottom-right-radius: 4px; }
    .ai-chat-bubble-ai { background: #fff; color: #292524; border: 1px solid rgba(28, 25, 23, 0.08); border-bottom-left-radius: 4px; white-space: pre-wrap; }
    .ai-chat-bubble-ai strong { color: #0f766e; }
    .ai-chat-input-area { display: flex; gap: 8px; padding: 12px; border-top: 1px solid rgba(28, 25, 23, 0.07); background: #fff; flex-shrink: 0; }
    .ai-chat-input {
        flex: 1; padding: 10px 14px; border: 1.5px solid rgba(28, 25, 23, 0.12); border-radius: 12px;
        font-size: 13px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; background: #fafaf9;
        color: #1c1917;
    }
    .ai-chat-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.14); background: #fff; }
    .ai-chat-input::placeholder { color: #a8a29e; }
    .ai-chat-send {
        width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #10b981 0%, #0d9488 100%);
        color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: opacity 0.15s, transform 0.15s; flex-shrink: 0;
    }
    .ai-chat-send:hover { transform: scale(1.05); }
    .ai-chat-send:disabled { opacity: 0.4; cursor: default; transform: none; }
    .ai-suggestion-chip {
        font-size: 12px; padding: 6px 12px; border-radius: 999px; border: 1px solid rgba(28, 25, 23, 0.12);
        background: #fff; color: #57534e; cursor: pointer; transition: all 0.15s; white-space: nowrap;
    }
    .ai-suggestion-chip:hover { border-color: #10b981; color: #0f766e; background: #ecfdf5; }
    .ai-typing-dot {
        width: 7px; height: 7px; border-radius: 50%; background: #a8a29e;
        animation: aiTypingBounce 0.6s ease-in-out infinite;
    }
    @keyframes aiTypingBounce {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30% { transform: translateY(-6px); opacity: 1; }
    }
    .dark .ai-chat-panel { background: #16181d; border-color: rgba(255, 255, 255, 0.08); box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.6); }
    .dark .ai-chat-header { background: #16181d; border-bottom-color: rgba(255, 255, 255, 0.08); color: #f5f5f4; }
    .dark .ai-chat-close { color: #a8a29e; }
    .dark .ai-chat-close:hover { background: rgba(255, 255, 255, 0.06); color: #f5f5f4; }
    .dark .ai-chat-messages { background: #101216; }
    .dark .ai-chat-bubble-ai { background: #1c1f26; border-color: rgba(255, 255, 255, 0.08); color: #e7e5e4; }
    .dark .ai-chat-bubble-ai strong { color: #5eead4; }
    .dark .ai-chat-input-area { background: #16181d; border-color: rgba(255, 255, 255, 0.08); }
    .dark .ai-chat-input { background: #101216; border-color: rgba(255, 255, 255, 0.12); color: #e7e5e4; }
    .dark .ai-chat-input:focus { background: #101216; }
    .dark .ai-chat-input::placeholder { color: #57534e; }
    .dark .ai-suggestion-chip { background: #1c1f26; border-color: rgba(255, 255, 255, 0.12); color: #a8a29e; }
    .dark .ai-suggestion-chip:hover { border-color: #10b981; color: #5eead4; background: rgba(16, 185, 129, 0.1); }

    @media (max-width: 440px) {
        .ai-chat-panel { width: calc(100vw - 32px); right: 16px; bottom: 76px; height: 460px; }
        .ai-chat-assistant { right: 16px; bottom: 16px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .ai-chat-fab, .ai-chat-send, .ai-suggestion-chip { transition: none; }
        .ai-typing-dot { animation-duration: 0.01ms; }
    }
</style>
@endonce

<div
    x-data="aiChatAssistant()"
    x-cloak
    class="ai-chat-assistant"
>
    <button
        x-show="!chatOpen"
        x-on:click="chatOpen = true"
        class="ai-chat-fab"
        aria-label="Buka asisten AI"
        title="Asisten AI crmoffice"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" aria-hidden="true">
            <path fill-rule="evenodd" d="M9 4.5a.75.75 0 01.721.544l.813 2.846a3.75 3.75 0 002.576 2.576l2.846.813a.75.75 0 010 1.442l-2.846.813a3.75 3.75 0 00-2.576 2.576l-.813 2.846a.75.75 0 01-1.442 0l-.813-2.846a3.75 3.75 0 00-2.576-2.576l-2.846-.813a.75.75 0 010-1.442l2.846-.813A3.75 3.75 0 007.466 7.89l.813-2.846A.75.75 0 019 4.5zM18 1.5a.75.75 0 01.728.568l.258 1.036c.236.94.97 1.674 1.91 1.91l1.036.258a.75.75 0 010 1.456l-1.036.258c-.94.236-1.674.97-1.91 1.91l-.258 1.036a.75.75 0 01-1.456 0l-.258-1.036a2.625 2.625 0 00-1.91-1.91l-1.036-.258a.75.75 0 010-1.456l1.036-.258a2.625 2.625 0 001.91-1.91l.258-1.036A.75.75 0 0118 1.5zM16.5 15a.75.75 0 01.712.513l.394 1.183c.15.447.5.799.948.948l1.183.395a.75.75 0 010 1.422l-1.183.395c-.447.15-.799.5-.948.948l-.395 1.183a.75.75 0 01-1.422 0l-.395-1.183a1.5 1.5 0 00-.948-.948l-1.183-.395a.75.75 0 010-1.422l1.183-.395a1.5 1.5 0 00.948-.948l.395-1.183A.75.75 0 0116.5 15z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-show="chatOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-3 scale-[0.98]"
        class="ai-chat-panel"
        role="dialog"
        aria-label="Asisten AI crmoffice"
    >
        <div class="ai-chat-header">
            <div class="flex items-center gap-2.5">
                <span class="ai-chat-header-chip" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                        <path fill-rule="evenodd" d="M9 4.5a.75.75 0 01.721.544l.813 2.846a3.75 3.75 0 002.576 2.576l2.846.813a.75.75 0 010 1.442l-2.846.813a3.75 3.75 0 00-2.576 2.576l-.813 2.846a.75.75 0 01-1.442 0l-.813-2.846a3.75 3.75 0 00-2.576-2.576l-2.846-.813a.75.75 0 010-1.442l2.846-.813A3.75 3.75 0 007.466 7.89l.813-2.846A.75.75 0 019 4.5z" clip-rule="evenodd" />
                    </svg>
                </span>
                <span class="font-semibold text-sm">Asisten AI crmoffice</span>
            </div>
            <button x-on:click="chatOpen = false" class="ai-chat-close" aria-label="Tutup asisten" title="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-[18px] h-[18px]" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div
            x-ref="messagesContainer"
            class="ai-chat-messages"
            aria-live="polite"
        >
            <template x-if="messages.length === 0">
                <div class="p-4">
                    <p class="text-sm text-stone-500 mb-3">Halo <span x-text="userName"></span>! Saya asisten AI crmoffice. Tanyakan apa saja tentang bisnis Anda.</p>
                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide mb-2">Coba tanyakan</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="s in suggestions" :key="s">
                            <button
                                x-on:click="sendSuggestion(s)"
                                class="ai-suggestion-chip"
                                x-text="s"
                            ></button>
                        </template>
                    </div>
                </div>
            </template>

            <template x-for="m in messages" :key="m.id">
                <div :class="m.role === 'user' ? 'ai-chat-msg-user' : 'ai-chat-msg-ai'">
                    <div class="ai-chat-bubble" :class="m.role === 'user' ? 'ai-chat-bubble-user' : 'ai-chat-bubble-ai'" x-html="m.content"></div>
                    <template x-if="m.chart">
                        <div class="mt-2 bg-white rounded-lg border border-stone-200 p-3">
                            <canvas :id="'chart-' + m.id" class="w-full" style="max-height: 160px;"></canvas>
                        </div>
                    </template>
                </div>
            </template>

            <div x-show="loading" class="ai-chat-msg-ai">
                <div class="ai-chat-bubble ai-chat-bubble-ai">
                    <div class="flex gap-1.5 py-1">
                        <span class="ai-typing-dot"></span>
                        <span class="ai-typing-dot" style="animation-delay:0.15s"></span>
                        <span class="ai-typing-dot" style="animation-delay:0.3s"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="ai-chat-input-area">
            <input
                type="text"
                x-model="input"
                x-on:keydown.enter="send()"
                placeholder="Ketik pesan..."
                class="ai-chat-input"
                aria-label="Pesan untuk asisten AI"
                :disabled="loading"
            />
            <button
                x-on:click="send()"
                class="ai-chat-send"
                aria-label="Kirim pesan"
                :disabled="loading || !input.trim()"
                x-show="!loading"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5" aria-hidden="true"><path d="M3.478 2.404a.75.75 0 00-.926.941l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.404z"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
    // Pastikan Chart.js tersedia (file lokal, tanpa CDN) untuk grafik di panel AI.
    (function ensureChartJs() {
        if (typeof window.Chart !== 'undefined') return;
        var s = document.createElement('script');
        s.src = '{{ asset('vendor/chartjs/chart.umd.min.js') }}';
        s.defer = true;
        document.head.appendChild(s);
    })();

    // Definisikan langsung & sinkron — jangan ditunda, Alpine butuh saat init.
    (function () {
        var _aiChatCharts = {};

        window.aiChatAssistant = function () {
            return {
                chatOpen: false,
                input: '',
                messages: [],
                loading: false,
                userName: @json(auth()->user()?->name ?? 'User'),
                currentPage: window.location.pathname,
                charts: _aiChatCharts,

                get suggestions() {
                    const pageMap = {
                        '/admin': ['Ringkasan bisnis hari ini', 'Task yang overdue', 'Invoice yang belum dibayar'],
                        '/admin/leads': ['Lead mana yang harus saya follow up?', 'Lead conversion rate', 'Top sales person'],
                        '/admin/invoices': ['Invoice overdue', 'Prediksi cashflow bulan ini', 'Revenue bulan ini'],
                        '/admin/projects': ['Project progress', 'Task saya hari ini', 'Project yang behind schedule'],
                        '/admin/tasks': ['Task saya hari ini', 'Task overdue', 'Task yang perlu difollow up'],
                        '/admin/tickets': ['Ticket open by priority', 'Ticket yang belum diassign', 'Ringkasan ticket hari ini'],
                        '/admin/clients': ['Client paling profitable', 'Client dengan invoice terbanyak', 'Client yang perlu dihubungi'],
                    };

                    for (const [path, suggestions] of Object.entries(pageMap)) {
                        if (this.currentPage.startsWith(path)) return suggestions;
                    }
                    return ['Ringkasan bisnis hari ini', 'Task yang overdue', 'Revenue bulan ini'];
                },

                sendSuggestion(text) {
                    this.input = text;
                    this.send();
                },

                async send() {
                    const msg = this.input.trim();
                    if (!msg || this.loading) return;

                    this.input = '';
                    this.messages.push({ id: Date.now(), role: 'user', content: this.escapeHtml(msg) });
                    this.loading = true;
                    this.$nextTick(() => this.scrollBottom());

                    try {
                        const resp = await fetch('/admin/ai/chat', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                            body: JSON.stringify({ message: msg, context: this.currentPage }),
                        });

                        if (!resp.ok) throw new Error('Network error');

                        const data = await resp.json();
                        this.messages.push({
                            id: Date.now() + 1,
                            role: 'ai',
                            content: this.formatMarkdown(data.answer || 'Maaf, saya tidak bisa merespon saat ini.'),
                            chart: data.chart_data || null,
                        });
                    } catch (e) {
                        this.messages.push({
                            id: Date.now() + 1,
                            role: 'ai',
                            content: 'Maaf, terjadi kesalahan koneksi. Coba lagi nanti.',
                            chart: null,
                        });
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => {
                            this.scrollBottom();
                            this.renderCharts();
                        });
                    }
                },

                formatMarkdown(text) {
                    return text
                        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.+?)\*/g, '<em>$1</em>')
                        .replace(/^(\d+)\.\s/gm, '<strong>$1.</strong> ')
                        .replace(/^- (.+)/gm, '&bull; $1')
                        .replace(/\n/g, '<br>');
                },

                escapeHtml(text) {
                    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                },

                scrollBottom() {
                    const el = this.$refs.messagesContainer;
                    if (el) el.scrollTop = el.scrollHeight;
                },

                renderCharts() {
                    if (typeof window.Chart === 'undefined') return;

                    const msgs = this.messages.filter(m => m.chart && m.chart.labels && m.chart.values);
                    msgs.forEach(m => {
                        const canvasId = 'chart-' + m.id;
                        const canvas = document.getElementById(canvasId);
                        if (!canvas) return;
                        if (this.charts[canvasId]) this.charts[canvasId].destroy();

                        const ctx = canvas.getContext('2d');
                        const type = m.chart.type || 'bar';
                        const colors = ['#10b981', '#0d9488', '#34d399', '#5eead4', '#2dd4bf', '#14b8a6', '#059669', '#0f766e'];

                        this.charts[canvasId] = new Chart(ctx, {
                            type: type,
                            data: {
                                labels: m.chart.labels,
                                datasets: [{
                                    label: m.chart.label || 'Data',
                                    data: m.chart.values,
                                    backgroundColor: type === 'doughnut' ? colors.slice(0, m.chart.values.length) : colors[0] + '99',
                                    borderColor: type === 'doughnut' ? '#fff' : colors[0],
                                    borderWidth: type === 'doughnut' ? 2 : 1,
                                    borderRadius: type === 'bar' ? 6 : 0,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: { legend: { display: type === 'doughnut', position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 10 } } } },
                                scales: type !== 'doughnut' ? {
                                    x: { display: true, ticks: { font: { size: 9 }, maxRotation: 45 } },
                                    y: { display: true, ticks: { font: { size: 9 } } },
                                } : {},
                            }
                        });
                    });
                },
            };
        };
    })();
</script>
@endauth
