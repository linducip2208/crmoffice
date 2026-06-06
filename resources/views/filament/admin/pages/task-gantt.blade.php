<x-filament-panels::page>
    <div style="margin-bottom:24px">
        <p style="color:#64748b;font-size:14px">Drag bars to reschedule tasks. Click to see details.</p>
    </div>

    @foreach($projects as $project)
        <div class="fi-section" style="margin-bottom:24px">
            <div class="fi-section-header">
                <h3 style="font-weight:700;font-size:17px">{{ $project['name'] }}</h3>
                <span class="fi-badge">{{ str_replace('_', ' ', ucfirst($project['status'])) }}</span>
            </div>
            <div class="fi-section-content-ctn">
                <div class="fi-section-content">
                    <div style="position:relative">
                        <svg id="gantt-{{ $project['id'] }}" style="width:100%;overflow-x:auto"></svg>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</x-filament-panels::page>

<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css">
<style>
.gantt .bar-label { font-family: 'Inter', sans-serif; font-size: 12px; }
.gantt .bar-progress { fill: rgba(79, 70, 229, 0.3); }
.gantt .bar-wrapper .bar { stroke: none; }
.gantt .bar-wrapper.active .bar { fill: #4f46e5 !important; }
.gantt .bar-wrapper .bar { fill: #7c3aed; rx: 4; ry: 4; }
.gantt .grid-header { fill: #f8fafc; stroke: #e5e7eb; font-family: 'Inter', sans-serif; font-size: 11px; }
.gantt .grid-row { fill: #ffffff; }
.gantt .row-line { stroke: #e5e7eb; stroke-width: 0.5; }
.gantt .tick { stroke: #e5e7eb; stroke-width: 0.5; }
.gantt .today-highlight { fill: rgba(79, 70, 229, 0.04); }
.dark .gantt .grid-header { fill: #0f1428; stroke: #1e2138; }
.dark .gantt .grid-row { fill: #11162a; }
.dark .gantt .row-line { stroke: #1e2138; }
.dark .gantt .tick { stroke: #1e2138; }
</style>
<script>
(function(){
    document.querySelectorAll('[id^=gantt-]').forEach(function(svg) {
        const project = {!! json_encode($projects) !!}.find(p => 'gantt-' + p.id === svg.id);
        if (!project || !project.tasks.length) return;

        const tasks = project.tasks.map(function(t) {
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
            on_click: function(task) { return true; },
            on_date_change: function(task, start, end) { return true; },
            custom_popup_html: function(task) {
                return '<div style="padding:8px 12px;font-family:Inter,sans-serif;font-size:13px"><strong>' + task.name + '</strong><br><span style="color:#64748b">' + task.start + ' → ' + task.end + '</span></div>';
            }
        });
    });
})();
</script>
