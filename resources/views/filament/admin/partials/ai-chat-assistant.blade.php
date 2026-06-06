@auth
<div
    x-data="aiChatAssistant()"
    x-cloak
    class="ai-chat-assistant"
>
    <button
        x-show="!open"
        x-on:click="open = true"
        class="ai-chat-fab"
        title="Asisten AI crmoffice"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
            <path fill-rule="evenodd" d="M9 4.5a.75.75 0 01.721.544l.813 2.846a3.75 3.75 0 002.576 2.576l2.846.813a.75.75 0 010 1.442l-2.846.813a3.75 3.75 0 00-2.576 2.576l-.813 2.846a.75.75 0 01-1.442 0l-.813-2.846a3.75 3.75 0 00-2.576-2.576l-2.846-.813a.75.75 0 010-1.442l2.846-.813A3.75 3.75 0 007.466 7.89l.813-2.846A.75.75 0 019 4.5zM18 1.5a.75.75 0 01.728.568l.258 1.036c.236.94.97 1.674 1.91 1.91l1.036.258a.75.75 0 010 1.456l-1.036.258c-.94.236-1.674.97-1.91 1.91l-.258 1.036a.75.75 0 01-1.456 0l-.258-1.036a2.625 2.625 0 00-1.91-1.91l-1.036-.258a.75.75 0 010-1.456l1.036-.258a2.625 2.625 0 001.91-1.91l.258-1.036A.75.75 0 0118 1.5zM16.5 15a.75.75 0 01.712.513l.394 1.183c.15.447.5.799.948.948l1.183.395a.75.75 0 010 1.422l-1.183.395c-.447.15-.799.5-.948.948l-.395 1.183a.75.75 0 01-1.422 0l-.395-1.183a1.5 1.5 0 00-.948-.948l-1.183-.395a.75.75 0 010-1.422l1.183-.395a1.5 1.5 0 00.948-.948l.395-1.183A.75.75 0 0116.5 15z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="ai-chat-panel"
    >
        <div class="ai-chat-header">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M9 4.5a.75.75 0 01.721.544l.813 2.846a3.75 3.75 0 002.576 2.576l2.846.813a.75.75 0 010 1.442l-2.846.813a3.75 3.75 0 00-2.576 2.576l-.813 2.846a.75.75 0 01-1.442 0l-.813-2.846a3.75 3.75 0 00-2.576-2.576l-2.846-.813a.75.75 0 010-1.442l2.846-.813A3.75 3.75 0 007.466 7.89l.813-2.846A.75.75 0 019 4.5z" clip-rule="evenodd" />
                </svg>
                <span class="font-semibold text-sm">Asisten AI crmoffice</span>
            </div>
            <button x-on:click="open = false" class="ai-chat-close" title="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-5 h-5" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div
            x-ref="messagesContainer"
            class="ai-chat-messages"
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
                :disabled="loading"
            />
            <button
                x-on:click="send()"
                class="ai-chat-send"
                :disabled="loading || !input.trim()"
                x-show="!loading"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M3.478 2.404a.75.75 0 00-.926.941l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.404z"/></svg>
            </button>
        </div>
    </div>
</div>
@endauth

<style>
    .ai-chat-assistant { position: fixed; bottom: 24px; right: 24px; z-index: 9999; font-family: 'Inter', system-ui, sans-serif; }
    .ai-chat-fab {
        width: 52px; height: 52px; border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #fff; border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.4);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .ai-chat-fab:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(79, 70, 229, 0.5); }
    .ai-chat-panel {
        position: fixed; bottom: 88px; right: 24px;
        width: 400px; max-height: 560px; height: 500px;
        background: #fff; border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
        display: flex; flex-direction: column; overflow: hidden;
    }
    .ai-chat-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 16px;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #fff; flex-shrink: 0;
    }
    .ai-chat-close { color: rgba(255,255,255,0.8); background: none; border: none; cursor: pointer; border-radius: 6px; padding: 4px; transition: background 0.15s; display: flex; }
    .ai-chat-close:hover { background: rgba(255,255,255,0.15); color: #fff; }
    .ai-chat-messages { flex: 1; overflow-y: auto; padding: 12px; background: #f8fafc; display: flex; flex-direction: column; gap: 8px; }
    .ai-chat-msg-user { display: flex; justify-content: flex-end; }
    .ai-chat-msg-ai { display: flex; justify-content: flex-start; }
    .ai-chat-bubble { max-width: 85%; padding: 10px 14px; border-radius: 14px; font-size: 13px; line-height: 1.55; word-break: break-word; }
    .ai-chat-bubble-user { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #fff; border-bottom-right-radius: 4px; }
    .ai-chat-bubble-ai { background: #fff; color: #1e293b; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px; white-space: pre-wrap; }
    .ai-chat-bubble-ai strong { color: #4338ca; }
    .ai-chat-input-area { display: flex; gap: 8px; padding: 12px; border-top: 1px solid #e2e8f0; background: #fff; flex-shrink: 0; }
    .ai-chat-input {
        flex: 1; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 12px;
        font-size: 13px; outline: none; transition: border-color 0.2s; background: #f8fafc;
    }
    .ai-chat-input:focus { border-color: #818cf8; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12); }
    .ai-chat-input::placeholder { color: #94a3b8; }
    .ai-chat-send {
        width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: opacity 0.15s, transform 0.15s; flex-shrink: 0;
    }
    .ai-chat-send:hover { transform: scale(1.05); }
    .ai-chat-send:disabled { opacity: 0.4; cursor: default; transform: none; }
    .ai-suggestion-chip {
        font-size: 12px; padding: 6px 12px; border-radius: 20px; border: 1px solid #e2e8f0;
        background: #fff; color: #475569; cursor: pointer; transition: all 0.15s; white-space: nowrap;
    }
    .ai-suggestion-chip:hover { border-color: #818cf8; color: #4338ca; background: #eef2ff; }
    .ai-typing-dot {
        width: 7px; height: 7px; border-radius: 50%; background: #94a3b8;
        animation: aiTypingBounce 0.6s ease-in-out infinite;
    }
    @keyframes aiTypingBounce {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30% { transform: translateY(-6px); opacity: 1; }
    }
    .dark .ai-chat-panel { background: #1e293b; box-shadow: 0 20px 60px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.06); }
    .dark .ai-chat-messages { background: #0f172a; }
    .dark .ai-chat-bubble-ai { background: #1e293b; border-color: #334155; color: #e2e8f0; }
    .dark .ai-chat-bubble-ai strong { color: #a5b4fc; }
    .dark .ai-chat-input-area { background: #1e293b; border-color: #334155; }
    .dark .ai-chat-input { background: #0f172a; border-color: #334155; color: #e2e8f0; }
    .dark .ai-chat-input::placeholder { color: #64748b; }
    .dark .ai-suggestion-chip { background: #1e293b; border-color: #334155; color: #94a3b8; }
    .dark .ai-suggestion-chip:hover { border-color: #818cf8; color: #a5b4fc; background: #1e1b4b; }
    .dark .ai-chat-bubble-user { background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%); }

    @media (max-width: 440px) {
        .ai-chat-panel { width: calc(100vw - 32px); right: 16px; bottom: 76px; height: 460px; }
        .ai-chat-fab { right: 16px; bottom: 16px; }
    }
</style>

<script>
    (function loadChartJs(cb) {
        if (typeof Chart !== 'undefined') return cb();
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js';
        s.onload = cb;
        document.head.appendChild(s);
    })(function () {

    function aiChatAssistant() {
        return {
            open: false,
            input: '',
            messages: [],
            loading: false,
            userName: '{{ auth()->user()?->name ?? 'User' }}',
            currentPage: window.location.pathname,
            charts: {},

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
                const msgs = this.messages.filter(m => m.chart && m.chart.labels && m.chart.values);
                msgs.forEach(m => {
                    const canvasId = 'chart-' + m.id;
                    const canvas = document.getElementById(canvasId);
                    if (!canvas) return;
                    if (this.charts[canvasId]) this.charts[canvasId].destroy();

                    const ctx = canvas.getContext('2d');
                    const type = m.chart.type || 'bar';
                    const colors = ['#4f46e5', '#7c3aed', '#a78bfa', '#c4b5fd', '#818cf8', '#6366f1', '#8b5cf6', '#a855f7'];

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
    }

    }); // end Chart.js loader
</script>
