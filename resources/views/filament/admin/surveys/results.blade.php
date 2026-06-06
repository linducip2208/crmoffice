<x-filament::page>
<style>
  .r-wrap{max-width:960px;margin:0 auto}
  .r-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:28px}
  .r-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;text-align:center}
  .r-card .val{font-size:32px;font-weight:800;color:#4f46e5;line-height:1.2}
  .r-card .lbl{font-size:13px;color:#64748b;margin-top:4px;font-weight:500}
  .r-q{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.03)}
  .r-q h3{font-size:17px;font-weight:700;color:#0f172a;margin-bottom:4px}
  .r-q .q-meta{font-size:12px;color:#94a3b8;margin-bottom:16px}
  .r-chart{max-width:100%;height:240px}
  .r-chart-sm{max-width:100%;height:300px}
  .r-bar{display:flex;align-items:center;gap:10px;margin-bottom:8px}
  .r-bar-lbl{width:60px;text-align:right;font-size:13px;font-weight:600;color:#475569;flex-shrink:0}
  .r-bar-track{flex:1;height:28px;background:#f1f5f9;border-radius:6px;overflow:hidden}
  .r-bar-fill{height:100%;border-radius:6px;transition:width .5s ease;min-width:2px;display:flex;align-items:center;padding-left:8px;font-size:11px;font-weight:700;color:#fff}
  .r-bar-pct{width:44px;font-size:12px;color:#64748b;flex-shrink:0}
  .r-avg{display:inline-flex;align-items:center;gap:12px;padding:10px 18px;background:var(--brand-light);border-radius:10px;margin-bottom:16px;font-size:15px;font-weight:700;color:#4f46e5}
  .r-avg .stars{color:#f59e0b}
  .r-nps{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:12px}
  .r-nps-box{flex:1;min-width:100px;text-align:center;padding:14px 10px;border-radius:10px}
  .r-nps-box .num{font-size:28px;font-weight:800}
  .r-nps-box .lbl{font-size:12px;color:#64748b;margin-top:2px}
  .r-nps-det{background:#fee2e2}.r-nps-det .num{color:#ef4444}
  .r-nps-pas{background:#fef3c7}.r-nps-pas .num{color:#f59e0b}
  .r-nps-pro{background:#dcfce7}.r-nps-pro .num{color:#22c55e}
  .r-text-list{max-height:300px;overflow-y:auto}
  .r-text-item{padding:10px 14px;background:#f8fafc;border:1px solid #f1f5f9;border-radius:8px;margin-bottom:6px;font-size:14px;color:#334155;font-style:italic}
  .r-empty{text-align:center;padding:60px 20px;color:#94a3b8}
  .r-empty svg{display:block;margin:0 auto 16px;opacity:.4}

  @media(max-width:640px){
    .r-summary{grid-template-columns:1fr 1fr}
    .r-chart,.r-chart-sm{height:200px}
    .r-bar-lbl{width:40px;font-size:12px}
  }
</style>

@php use App\Filament\Admin\Resources\Surveys\Pages\ViewSurveyResults; $page = new ViewSurveyResults; $page->record = $record; $results = $page->results(); $totalResponses = $record->responses->count(); @endphp

<div class="r-wrap">
  {{-- Summary Cards --}}
  <div class="r-summary">
    <div class="r-card">
      <div class="val">{{ $totalResponses }}</div>
      <div class="lbl">Total Respons</div>
    </div>
    <div class="r-card">
      <div class="val">{{ $record->questions->count() }}</div>
      <div class="lbl">Total Pertanyaan</div>
    </div>
    @php $completionRate = $record->questions->count() > 0 ? round(array_sum(array_map(fn($r) => $r['total_responses'] > 0 ? 1 : 0, $results)) / count($results) * 100) : 0; @endphp
    <div class="r-card">
      <div class="val">{{ $completionRate }}%</div>
      <div class="lbl">Tingkat Pengisian</div>
    </div>
  </div>

  @if(empty($results))
    <div class="r-empty">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
      <p style="font-size:15px">Belum ada respons untuk survey ini.</p>
    </div>
  @endif

  @foreach($results as $r)
    <div class="r-q">
      <h3>{{ $r['question'] }}</h3>
      <div class="q-meta">{{ $r['type'] }} · {{ $r['total_responses'] }} jawaban</div>

      {{-- Rating --}}
      @if($r['type'] === 'rating' && $r['average'] !== null)
        @php $fullStars = round($r['average']); @endphp
        <div class="r-avg">
          <span>Rata-rata: {{ $r['average'] }}</span>
          <span class="stars" style="color:#f59e0b">
            @for($s=1;$s<=5;$s++){{ $s <= $fullStars ? '★' : '☆' }}@endfor
          </span>
        </div>
        <div class="r-chart-sm">
          <canvas id="ratingChart{{ $r['id'] }}"></canvas>
        </div>
        <script>
          (function(){
            var ctx = document.getElementById('ratingChart{{ $r['id'] }}');
            if(!ctx) return;
            var labels = {!! json_encode(array_map(fn($d) => $d['stars'], $r['distribution'])) !!};
            var data = {!! json_encode(array_map(fn($d) => $d['count'], $r['distribution'])) !!};
            var colors = ['#ef4444','#f97316','#f59e0b','#84cc16','#22c55e'];
            new Chart(ctx, {
              type: 'bar',
              data: {
                labels: labels,
                datasets: [{ data: data, backgroundColor: colors, borderRadius: 6 }]
              },
              options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
              }
            });
          })();
        </script>
      @endif

      {{-- NPS --}}
      @if($r['type'] === 'nps' && $r['nps_score'] !== null)
        <div class="r-nps">
          <div class="r-nps-box r-nps-det">
            <div class="num">{{ $r['detractor_pct'] }}%</div>
            <div class="lbl">Detractor</div>
          </div>
          <div class="r-nps-box r-nps-pas">
            <div class="num">{{ $r['passive_pct'] }}%</div>
            <div class="lbl">Passive</div>
          </div>
          <div class="r-nps-box r-nps-pro">
            <div class="num">{{ $r['promoter_pct'] }}%</div>
            <div class="lbl">Promoter</div>
          </div>
          <div style="text-align:center;min-width:80px">
            <div style="font-size:36px;font-weight:800;color:#4f46e5">{{ $r['nps_score'] }}</div>
            <div style="font-size:12px;color:#64748b">NPS Score</div>
          </div>
        </div>
        <div class="r-chart">
          <canvas id="npsChart{{ $r['id'] }}"></canvas>
        </div>
        <script>
          (function(){
            var ctx = document.getElementById('npsChart{{ $r['id'] }}');
            if(!ctx) return;
            var labels = {!! json_encode(array_map(fn($d) => $d['option'], $r['distribution'])) !!};
            var data = {!! json_encode(array_map(fn($d) => $d['count'], $r['distribution'])) !!};
            var colors = labels.map(function(v){ return v<=6?'#ef4444':(v<=8?'#f59e0b':'#22c55e'); });
            new Chart(ctx, {
              type: 'bar',
              data: {
                labels: labels,
                datasets: [{ data: data, backgroundColor: colors, borderRadius: 4 }]
              },
              options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
              }
            });
          })();
        </script>
      @endif

      {{-- Single / Multiple Choice --}}
      @if(in_array($r['type'], ['single_choice','select','multiple_choice']) && !empty($r['distribution']))
        <div class="r-chart-sm">
          <canvas id="choiceChart{{ $r['id'] }}"></canvas>
        </div>
        <script>
          (function(){
            var ctx = document.getElementById('choiceChart{{ $r['id'] }}');
            if(!ctx) return;
            var labels = {!! json_encode(array_map(fn($d) => $d['option'], $r['distribution'])) !!};
            var data = {!! json_encode(array_map(fn($d) => $d['count'], $r['distribution'])) !!};
            new Chart(ctx, {
              type: 'doughnut',
              data: {
                labels: labels,
                datasets: [{ data: data, backgroundColor: ['#4f46e5','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#6366f1','#14b8a6','#84cc16'] }]
              },
              options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                  legend: { position: 'right', labels: { boxWidth: 12, padding: 12, font: { size: 12 } } }
                }
              }
            });
          })();
        </script>
      @endif

      {{-- Text / Textarea --}}
      @if(in_array($r['type'], ['text','textarea']) && !empty($r['word_cloud']))
        <div class="r-text-list">
          @foreach($r['word_cloud'] as $t)
            <div class="r-text-item">&ldquo;{{ $t }}&rdquo;</div>
          @endforeach
        </div>
      @endif

      {{-- Fallback bars for any distribution without chart --}}
      @if(!in_array($r['type'], ['text','textarea']) && !in_array($r['type'], ['rating','nps','single_choice','select','multiple_choice']) && !empty($r['distribution']))
        @foreach($r['distribution'] as $d)
          <div class="r-bar">
            <span class="r-bar-lbl">{{ $d['option'] ?? $d['stars'] ?? '' }}</span>
            <div class="r-bar-track">
              <div class="r-bar-fill" style="width:{{ $d['percentage'] }}%;background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                {{ $d['count'] }}
              </div>
            </div>
            <span class="r-bar-pct">{{ $d['percentage'] }}%</span>
          </div>
        @endforeach
      @endif
    </div>
  @endforeach
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
</x-filament::page>
