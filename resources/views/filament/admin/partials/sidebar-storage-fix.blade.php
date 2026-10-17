{{-- Workaround: Filament 5.10.2 inits sidebar collapsedGroups with $persist(null),
     so groupIsCollapsed() throws "Cannot read properties of null (reading 'includes')"
     on fresh browsers and the whole sidebar nav stays hidden. Seed + guard it. --}}
<script>
(function () {
    var KEY = 'filament:sidebar:collapsed_groups';

    function sanitized() {
        try {
            var raw = localStorage.getItem(KEY);
            if (raw === null || raw === 'null' || raw === 'undefined') return [];
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    try {
        localStorage.setItem(KEY, JSON.stringify(sanitized()));
    } catch (e) {}

    document.addEventListener('alpine:init', function () {
        try {
            var sidebar = window.Alpine && window.Alpine.store
                ? window.Alpine.store('sidebar')
                : null;
            if (sidebar && !Array.isArray(sidebar.collapsedGroups)) {
                sidebar.collapsedGroups = sanitized();
            }
        } catch (e) {}
    });
})();
</script>
