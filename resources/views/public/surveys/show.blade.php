@extends('public._layout', ['title' => $survey->title, 'appName' => 'crmoffice'])

@section('content')
<style>
  :root{--brand:#4f46e5;--brand-light:#eef2ff}
  .survey-wrap{max-width:700px}
  .s-head{margin-bottom:20px}
  .s-head h1{font-size:28px;font-weight:800;color:#0f172a;letter-spacing:-.02em;line-height:1.2}
  .s-head p{color:#475569;font-size:15px;margin-top:6px;line-height:1.6}

  .pbar-wrap{display:flex;align-items:center;gap:12px;margin-bottom:32px}
  .pbar-step{font-size:13px;font-weight:600;color:#64748b;white-space:nowrap}
  .pbar-track{flex:1;height:8px;background:#e2e8f0;border-radius:99px;overflow:hidden}
  .pbar-fill{height:100%;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:99px;transition:width .4s cubic-bezier(.16,1,.3,1)}

  .q-block{display:none;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:28px;box-shadow:0 1px 3px rgba(0,0,0,.03);animation:fadeIn .3s ease}
  .q-block.active{display:block}
  @keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
  .q-label{display:flex;align-items:center;font-size:18px;font-weight:700;color:#0f172a;margin-bottom:18px;line-height:1.4}
  .q-req{color:#dc2626;margin-left:4px;font-weight:700}
  .q-input{width:100%;padding:12px 16px;border:1.5px solid #cbd5e1;border-radius:10px;font-size:15px;font-family:'Inter',system-ui,sans-serif;color:#0f172a;transition:border-color .15s,box-shadow .15s}
  .q-input:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px rgba(79,70,229,.12)}
  .q-textarea{resize:vertical;min-height:120px}
  .q-opt{display:flex;align-items:center;gap:12px;padding:12px 16px;border:1.5px solid #e5e7eb;border-radius:10px;margin-bottom:8px;cursor:pointer;transition:all .15s;font-size:14px}
  .q-opt:hover{border-color:#c7d2fe;background:#f8fafc}
  .q-opt input[type="radio"],.q-opt input[type="checkbox"]{accent-color:var(--brand);width:18px;height:18px;cursor:pointer;flex-shrink:0;margin:0}
  .q-opt:has(input:checked){border-color:var(--brand);background:var(--brand-light)}

  .stars{display:flex;gap:6px;flex-direction:row-reverse;justify-content:flex-end}
  .stars label{cursor:pointer;font-size:0}
  .stars input{display:none}
  .stars svg{width:40px;height:40px;transition:all .15s}
  .stars label:hover svg,.stars label:hover ~ label svg{fill:#fbbf24;stroke:#f59e0b}
  .stars:has(input:checked) label svg{fill:#e2e8f0;stroke:#cbd5e1}
  .stars input:checked ~ label svg{fill:#fbbf24!important;stroke:#f59e0b!important}
  .star-lbls{display:flex;justify-content:space-between;max-width:260px;font-size:12px;color:#94a3b8;margin-top:6px}

  .nps-grid{display:flex;gap:4px;flex-wrap:wrap}
  .nps-btn{display:flex;align-items:center;justify-content:center;width:42px;height:42px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:all .12s;color:#334155;background:#fff}
  .nps-btn:hover{border-color:var(--brand);color:var(--brand)}
  .nps-btn input{display:none}
  .nps-btn:has(input:checked){background:var(--brand);color:#fff;border-color:var(--brand)}
  .nps-lbls{display:flex;justify-content:space-between;font-size:12px;color:#94a3b8;margin-top:4px}
  .nps-zones{display:flex;gap:3px;margin-top:10px;border-radius:6px;overflow:hidden;height:5px}
  .nps-zone-d{background:#ef4444;flex:7}.nps-zone-p{background:#f59e0b;flex:2}.nps-zone-pr{background:#22c55e;flex:2}
  .nps-zone-lbl{display:flex;justify-content:space-between;font-size:10px;color:#94a3b8;margin-top:4px}

  .q-err{font-size:13px;color:#dc2626;margin-top:8px}.q-help{font-size:12px;color:#94a3b8;margin-top:6px}

  .nav-btns{display:flex;justify-content:space-between;align-items:center;margin-top:24px}
  .btn-prev,.btn-next,.btn-submit{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:10px;font-size:15px;font-weight:600;font-family:inherit;cursor:pointer;transition:all .2s;border:none}
  .btn-prev{background:#fff;color:#475569;border:1.5px solid #e5e7eb}
  .btn-prev:hover{border-color:#cbd5e1;background:#f8fafc}
  .btn-next{background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;box-shadow:0 4px 14px rgba(79,70,229,.25)}
  .btn-next:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(79,70,229,.35)}
  .btn-submit{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;box-shadow:0 4px 14px rgba(34,197,94,.25)}
  .btn-submit:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(34,197,94,.35)}

  .q-indicator{margin-bottom:28px;font-size:14px;font-weight:600;color:#475569}
  .req-hint{font-size:13px;color:#94a3b8}

  @media(max-width:640px){
    .s-head h1{font-size:22px}
    .q-block{padding:18px 16px}
    .q-label{font-size:16px}
    .nps-btn{width:34px;height:34px;font-size:12px}
    .stars svg{width:34px;height:34px}
    .nav-btns{flex-wrap:wrap;gap:10px}
    .btn-prev,.btn-next,.btn-submit{flex:1;justify-content:center;padding:12px 16px}
  }
</style>

<div class="survey-wrap">
  <div class="s-head">
    <h1>{{ $survey->title }}</h1>
    @if($survey->description)
      <p>{{ $survey->description }}</p>
    @endif
  </div>

  <div class="pbar-wrap">
    <div class="pbar-track">
      <div class="pbar-fill" id="pbar-fill" style="width:0%"></div>
    </div>
    <span class="pbar-step" id="pbar-text"></span>
  </div>

  @if($errors->any())
    <div class="alert alert-error" style="margin-bottom:20px">
      Mohon lengkapi semua pertanyaan wajib sebelum mengirim.
    </div>
  @endif

  <form method="POST" action="{{ route('public.surveys.submit', $survey->public_token) }}" id="survey-form">
    @csrf
    @php $total = $survey->questions->count(); $idx = 0; @endphp

    @foreach($survey->questions as $i => $q)
      @php $idx++; @endphp
      <div class="q-block{{ $idx === 1 ? ' active' : '' }}" data-step="{{ $idx }}" data-total="{{ $total }}">
        <div class="q-label">
          {{ $q->question }}@if($q->is_required)<span class="q-req">*</span>@endif
        </div>

        {{-- Text --}}
        @if($q->type === 'text')
          <input class="q-input" type="text" name="q_{{ $q->id }}" id="q_{{ $q->id }}" maxlength="500"
                 @if($q->is_required) required @endif value="{{ old('q_'. $q->id) }}">
          @error('q_'.$q->id)<div class="q-err">{{ $message }}</div>@enderror

        {{-- Textarea --}}
        @elseif($q->type === 'textarea')
          <textarea class="q-input q-textarea" name="q_{{ $q->id }}" id="q_{{ $q->id }}" rows="4"
                    @if($q->is_required) required @endif>{{ old('q_'. $q->id) }}</textarea>
          @error('q_'.$q->id)<div class="q-err">{{ $message }}</div>@enderror

        {{-- Single choice --}}
        @elseif($q->type === 'single_choice')
          @foreach((array) $q->options as $opt)
            @php $val = is_array($opt) ? ($opt['value'] ?? '') : $opt; $label = is_array($opt) ? ($opt['label'] ?? $val) : $opt; @endphp
            <label class="q-opt">
              <input type="radio" name="q_{{ $q->id }}" value="{{ $val }}"
                     @if($q->is_required) required @endif
                     @if(old('q_'.$q->id) == $val) checked @endif>
              <span>{{ $label }}</span>
            </label>
          @endforeach
          @error('q_'.$q->id)<div class="q-err">{{ $message }}</div>@enderror

        {{-- Multiple choice --}}
        @elseif($q->type === 'multiple_choice')
          @php $oldVals = old('q_'.$q->id, []); @endphp
          @foreach((array) $q->options as $opt)
            @php $val = is_array($opt) ? ($opt['value'] ?? '') : $opt; $label = is_array($opt) ? ($opt['label'] ?? $val) : $opt; @endphp
            <label class="q-opt">
              <input type="checkbox" name="q_{{ $q->id }}[]" value="{{ $val }}"
                     @if(in_array($val, $oldVals)) checked @endif>
              <span>{{ $label }}</span>
            </label>
          @endforeach
          <div class="q-help">Pilih semua yang sesuai.</div>
          @error('q_'.$q->id)<div class="q-err">{{ $message }}</div>@enderror

        {{-- Rating --}}
        @elseif($q->type === 'rating')
          <div class="stars">
            @for($n = 5; $n >= 1; $n--)
              <label>
                <input type="radio" name="q_{{ $q->id }}" value="{{ $n }}"
                       @if($q->is_required) required @endif
                       @if(old('q_'.$q->id) == $n) checked @endif>
                <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              </label>
            @endfor
          </div>
          <div class="star-lbls"><span>Buruk</span><span>Luar Biasa</span></div>
          @error('q_'.$q->id)<div class="q-err">{{ $message }}</div>@enderror

        {{-- NPS --}}
        @elseif($q->type === 'nps')
          <div class="nps-grid">
            @for($n = 0; $n <= 10; $n++)
              <label class="nps-btn">
                <input type="radio" name="q_{{ $q->id }}" value="{{ $n }}"
                       @if($q->is_required) required @endif
                       @if(old('q_'.$q->id) === (string)$n) checked @endif>
                {{ $n }}
              </label>
            @endfor
          </div>
          <div class="nps-lbls"><span>Tidak mungkin merekomendasikan</span><span>Sangat mungkin</span></div>
          <div class="nps-zones"><div class="nps-zone-d"></div><div class="nps-zone-p"></div><div class="nps-zone-pr"></div></div>
          <div class="nps-zone-lbl"><span>Detractor 0-6</span><span>Passive 7-8</span><span>Promoter 9-10</span></div>
          @error('q_'.$q->id)<div class="q-err">{{ $message }}</div>@enderror

        {{-- Fallback --}}
        @else
          <input class="q-input" type="text" name="q_{{ $q->id }}" id="q_{{ $q->id }}"
                 value="{{ old('q_'. $q->id) }}"
                 @if($q->is_required) required @endif>
          @error('q_'.$q->id)<div class="q-err">{{ $message }}</div>@enderror
        @endif

        <div class="q-indicator">Pertanyaan {{ $idx }} dari {{ $total }}</div>
        <div class="nav-btns">
          <div>
            @if($idx > 1)
              <button type="button" class="btn-prev" onclick="prevStep()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Sebelumnya
              </button>
            @endif
          </div>
          <div>
            @if($idx < $total)
              <button type="button" class="btn-next" onclick="nextStep()">
                Selanjutnya
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
            @else
              <button type="submit" class="btn-submit">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Kirim Jawaban
              </button>
            @endif
          </div>
        </div>
        @if($idx === $total)
          <div style="text-align:center;margin-top:12px" class="req-hint"><span style="color:#dc2626">*</span> Wajib diisi</div>
        @endif
      </div>
    @endforeach
  </form>
</div>

<script>
  let currentStep = 1;
  const total = {{ $total }};

  function showStep(n) {
    document.querySelectorAll('.q-block').forEach(function(b) {
      b.classList.remove('active');
      const step = parseInt(b.dataset.step);
      if (step === n) {
        b.classList.add('active');
        b.style.display = 'block';
      } else {
        b.style.display = 'none';
      }
    });
    updateProgress();
    document.getElementById('survey-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function nextStep() {
    const block = document.querySelector('.q-block[data-step="' + currentStep + '"]');
    if (!validateStep(block)) return;
    if (currentStep < total) {
      currentStep++;
      showStep(currentStep);
    }
  }

  function prevStep() {
    if (currentStep > 1) {
      currentStep--;
      showStep(currentStep);
    }
  }

  function validateStep(block) {
    const required = block.querySelector('[required]');
    if (!required) return true;
    const radioChecked = block.querySelector('input[type="radio"]:checked');
    const checkboxChecked = block.querySelector('input[type="checkbox"]:checked');

    if (required.type === 'radio') {
      if (!radioChecked) {
        alert('Silakan pilih jawaban untuk pertanyaan ini.');
        return false;
      }
    } else if (required.type === 'checkbox') {
      if (!checkboxChecked) {
        alert('Silakan pilih minimal satu opsi.');
        return false;
      }
    } else {
      if (!required.value.trim()) {
        alert('Silakan isi jawaban untuk pertanyaan ini.');
        required.focus();
        return false;
      }
    }
    return true;
  }

  function updateProgress() {
    let answered = 0;
    document.querySelectorAll('.q-block').forEach(function(block) {
      const radios = block.querySelectorAll('input[type="radio"]');
      const checkboxes = block.querySelectorAll('input[type="checkbox"]');
      const texts = block.querySelectorAll('input[type="text"], textarea');

      if (radios.length && block.querySelector('input[type="radio"]:checked')) answered++;
      else if (checkboxes.length && block.querySelector('input[type="checkbox"]:checked')) answered++;
      else if (!radios.length && !checkboxes.length) {
        let filled = false;
        texts.forEach(function(t) { if (t.value.trim()) filled = true; });
        if (filled) answered++;
      }
    });

    const pct = total ? Math.round(answered / total * 100) : 0;
    document.getElementById('pbar-fill').style.width = pct + '%';
    document.getElementById('pbar-text').textContent = answered + '/' + total + ' terjawab';
  }

  if (total > 0) showStep(1);
  updateProgress();
</script>
@endsection
