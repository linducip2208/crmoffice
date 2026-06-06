<div class="p-6 space-y-8">

  @php $results = $this->results(); @endphp

  @if(empty($results))
    <div class="flex flex-col items-center justify-center py-16 text-center">
      <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
      <p class="text-lg font-semibold text-gray-500">Belum ada respons</p>
      <p class="text-sm text-gray-400 mt-1">Bagikan link survey untuk mulai mengumpulkan data.</p>
    </div>
  @endif

  @foreach($results as $r)
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h3 class="text-base font-semibold text-gray-900">{{ $r['question'] }}</h3>
            <p class="text-xs text-gray-500 mt-1">
              {{ $r['total_responses'] }} respons &middot; Tipe: <span class="uppercase font-medium text-gray-600">{{ $r['type'] }}</span>
              @if($r['is_required']) <span class="text-red-500 ml-1">*</span> @endif
              @if($r['average'] !== null) &middot; Rata-rata: <strong class="text-indigo-600">{{ $r['average'] }}</strong> @endif
              @if($r['nps_score'] !== null) &middot; NPS: <strong class="text-indigo-600">{{ $r['nps_score'] }}</strong> @endif
            </p>
          </div>
        </div>
      </div>

      <div class="p-5">

        {{-- Text / Textarea — show sample answers --}}
        @if(in_array($r['type'], ['text', 'textarea']))
          @if(!empty($r['word_cloud']))
            <div class="space-y-2 max-h-80 overflow-y-auto">
              @foreach($r['word_cloud'] as $text)
                <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm text-gray-700 border border-gray-100">{{ $text }}</div>
              @endforeach
            </div>
          @else
            <p class="text-sm text-gray-400 italic">Tidak ada jawaban teks.</p>
          @endif

        {{-- Single choice / Select — bar chart per option --}}
        @elseif(in_array($r['type'], ['single_choice', 'select']))
          @if(!empty($r['distribution']))
            <div class="space-y-3">
              @foreach($r['distribution'] as $d)
                <div>
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ $d['option'] }}</span>
                    <span class="text-sm text-gray-500">{{ $d['count'] }} ({{ $d['percentage'] }}%)</span>
                  </div>
                  <div class="w-full bg-gray-100 rounded-full h-5 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500 transition-all duration-500 flex items-center"
                         style="width:{{ $d['percentage'] }}%">
                      <span class="text-xs text-white font-semibold px-2 leading-none">{{ $d['percentage'] }}%</span>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-sm text-gray-400 italic">Belum ada pilihan dijawab.</p>
          @endif

        {{-- Multiple choice — bar chart per option --}}
        @elseif($r['type'] === 'multiple_choice')
          @if(!empty($r['distribution']))
            <div class="space-y-3">
              @foreach($r['distribution'] as $d)
                <div>
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ $d['option'] }}</span>
                    <span class="text-sm text-gray-500">{{ $d['count'] }} ({{ $d['percentage'] }}%)</span>
                  </div>
                  <div class="w-full bg-gray-100 rounded-full h-5 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-500 flex items-center"
                         style="width:{{ $d['percentage'] }}%">
                      <span class="text-xs text-white font-semibold px-2 leading-none">{{ $d['percentage'] }}%</span>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-sm text-gray-400 italic">Belum ada pilihan dijawab.</p>
          @endif

        {{-- Rating 1-5 — star distribution + average --}}
        @elseif($r['type'] === 'rating')
          @if($r['average'] !== null)
            <div class="mb-4 flex items-center gap-2">
              <span class="text-3xl font-extrabold text-indigo-600">{{ $r['average'] }}</span>
              <div class="flex items-center gap-0.5 text-amber-400">
                @for($s = 1; $s <= 5; $s++)
                  <svg class="w-6 h-6" fill="{{ $s <= round($r['average']) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                @endfor
              </div>
              <span class="text-sm text-gray-500 ml-2">dari {{ $r['total_responses'] }} penilaian</span>
            </div>
          @endif
          @if(!empty($r['distribution']))
            <div class="space-y-2">
              @foreach($r['distribution'] as $d)
                <div class="flex items-center gap-3">
                  <span class="text-xs text-gray-500 w-20 text-right shrink-0">{{ $d['stars'] }}</span>
                  <div class="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-orange-400 transition-all duration-500 flex items-center"
                         style="width:{{ $d['percentage'] }}%">
                      <span class="text-xs text-white font-semibold px-2 leading-none">{{ $d['percentage'] }}%</span>
                    </div>
                  </div>
                  <span class="text-sm text-gray-500 w-8 text-right shrink-0">{{ $d['count'] }}</span>
                </div>
              @endforeach
            </div>
          @endif

        {{-- NPS 0-10 — gauge + distribution --}}
        @elseif($r['type'] === 'nps')
          @if($r['nps_score'] !== null)
            <div class="mb-6">
              <div class="flex items-end gap-4 mb-2">
                <span class="text-4xl font-extrabold {{ $r['nps_score'] >= 50 ? 'text-emerald-600' : ($r['nps_score'] >= 0 ? 'text-amber-500' : 'text-red-500') }}">{{ $r['nps_score'] }}</span>
                <span class="text-sm text-gray-500 mb-1">NPS Score</span>
              </div>

              {{-- NPS gauge bar --}}
              <div class="w-full bg-gray-100 rounded-full h-8 overflow-hidden flex">
                <div class="h-full bg-red-500 transition-all duration-500 flex items-center justify-center text-xs text-white font-bold"
                     style="width:{{ $r['detractor_pct'] }}%">{{ $r['detractor_pct'] }}%</div>
                <div class="h-full bg-amber-400 transition-all duration-500 flex items-center justify-center text-xs text-white font-bold"
                     style="width:{{ $r['passive_pct'] }}%">{{ $r['passive_pct'] }}%</div>
                <div class="h-full bg-emerald-500 transition-all duration-500 flex items-center justify-center text-xs text-white font-bold"
                     style="width:{{ $r['promoter_pct'] }}%">{{ $r['promoter_pct'] }}%</div>
              </div>
              <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>Detractor (0-6): {{ $r['detractors'] }}</span>
                <span>Passive (7-8): {{ $r['passives'] }}</span>
                <span>Promoter (9-10): {{ $r['promoters'] }}</span>
              </div>
            </div>
          @endif

          @if(!empty($r['distribution']))
            <div class="space-y-1">
              @foreach($r['distribution'] as $d)
                <div class="flex items-center gap-2">
                  <span class="text-xs font-semibold w-5 text-right shrink-0 {{ $d['zone'] === 'detractor' ? 'text-red-500' : ($d['zone'] === 'passive' ? 'text-amber-500' : 'text-emerald-600') }}">{{ $d['option'] }}</span>
                  <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500
                         {{ $d['zone'] === 'detractor' ? 'bg-red-400' : ($d['zone'] === 'passive' ? 'bg-amber-400' : 'bg-emerald-500') }}"
                         style="width:{{ $d['percentage'] }}%"></div>
                  </div>
                  <span class="text-xs text-gray-500 w-8 text-right shrink-0">{{ $d['count'] }}</span>
                </div>
              @endforeach
            </div>
          @endif
        @endif
      </div>
    </div>
  @endforeach

  @if(!empty($results))
    <div class="text-center text-xs text-gray-400 py-4">
      Total {{ $this->record->responses->count() }} respons — data real-time
    </div>
  @endif
</div>
