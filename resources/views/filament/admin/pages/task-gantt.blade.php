<x-filament-panels::page>
    <div style="margin-bottom:20px">
        <p style="color:#78716c;font-size:13.5px;margin:0">
            Drag bar untuk mengubah jadwal task — perubahan tersimpan otomatis. Klik bar untuk melihat detail jadwal.
            @if ($totalProjects > count($projects))
                <span style="color:#a8a29e">Menampilkan {{ count($projects) }} project terbaru dari total {{ $totalProjects }} project aktif.</span>
            @endif
        </p>
    </div>

    @if (empty($projects))
        <div class="fi-section" style="padding:28px">
            <p style="color:#78716c;margin:0">Belum ada project aktif dengan task. Buat project terlebih dahulu untuk melihat timeline.</p>
        </div>
    @endif

    @foreach ($projects as $project)
        <div class="fi-section" style="margin-bottom:20px">
            <div class="fi-section-header">
                <h3 style="font-weight:700;font-size:16px;margin:0">{{ $project['name'] }}</h3>
                <span class="fi-badge">{{ ucwords(str_replace('_', ' ', $project['status'])) }}</span>
            </div>
            <div class="fi-section-content-ctn">
                <div class="fi-section-content">
                    <svg id="gantt-{{ $project['id'] }}" style="width:100%"></svg>
                </div>
            </div>
        </div>
    @endforeach

    {{-- frappe-gantt self-hosted (tanpa CDN). CSS-nya ada di theme.css (Vite build). --}}
    <script src="{{ asset('vendor/frappe-gantt/frappe-gantt.min.js') }}"></script>
    <script>
        (function () {
            const projects = @json($projects);

            function fmt(d) {
                const x = (d instanceof Date) ? d : new Date(d);
                const m = String(x.getMonth() + 1).padStart(2, '0');
                const day = String(x.getDate()).padStart(2, '0');
                return x.getFullYear() + '-' + m + '-' + day;
            }

            function initChart(svg) {
                const project = projects.find(function (p) { return 'gantt-' + p.id === svg.id; });
                if (!project || !project.tasks || !project.tasks.length) return;

                const tasks = project.tasks.map(function (t) {
                    return {
                        id: String(t.id),
                        name: t.title,
                        start: t.start,
                        end: t.end,
                        progress: t.progress,
                        dependencies: t.dependencies || '',
                    };
                });

                new Gantt(svg, tasks, {
                    view_mode: 'Week',
                    date_format: 'YYYY-MM-DD',
                    on_date_change: function (task, start, end) {
                        const wireEl = svg.closest('[wire\\:id]');
                        if (!wireEl || !window.Livewire) return;
                        const comp = window.Livewire.find(wireEl.getAttribute('wire:id'));
                        if (comp) comp.call('updateTaskDate', Number(task.id), fmt(start), fmt(end));
                    },
                    custom_popup_html: function (task) {
                        return '<div style="padding:10px 14px;font-family:Inter,sans-serif;font-size:13px">' +
                            '<strong>' + task.name + '</strong><br>' +
                            '<span style="opacity:.65">' + task.start + ' → ' + task.end + '</span>' +
                            '</div>';
                    },
                });
            }

            // Render lazily per chart supaya halaman tetap responsif
            // (project bisa puluhan — jangan render semua sekaligus).
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            observer.unobserve(entry.target);
                            initChart(entry.target);
                        }
                    });
                }, { rootMargin: '240px 0px' });

                document.querySelectorAll('svg[id^=gantt-]').forEach(function (svg) {
                    observer.observe(svg);
                });
            } else {
                document.querySelectorAll('svg[id^=gantt-]').forEach(function (svg) {
                    setTimeout(function () { initChart(svg); }, 0);
                });
            }
        })();
    </script>
</x-filament-panels::page>
