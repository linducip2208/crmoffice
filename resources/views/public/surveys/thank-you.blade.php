@extends('public._layout', ['title' => 'Terima Kasih — ' . $survey->title, 'appName' => 'crmoffice'])

@section('content')
<style>
  .ty-wrap { text-align: center; padding: 40px 0; }
  .ty-icon { display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 99px; background: linear-gradient(135deg, #4f46e5, #7c3aed); margin-bottom: 24px; }
  .ty-icon svg { width: 36px; height: 36px; stroke: #fff; }
  .ty-wrap h1 { font-size: 28px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; margin-bottom: 12px; }
  .ty-wrap p { font-size: 16px; color: #475569; line-height: 1.6; max-width: 460px; margin: 0 auto 32px; }
  .ty-meta { display: inline-flex; flex-direction: column; align-items: center; gap: 4px; padding: 16px 28px; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; color: #64748b; }
  .ty-meta strong { color: #0f172a; }
  @media (max-width: 640px) {
    .ty-wrap { padding: 24px 0; }
    .ty-wrap h1 { font-size: 22px; }
  }
</style>

<div class="ty-wrap">
  <div class="ty-icon">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  </div>
  <h1>Terima Kasih!</h1>
  <p>Jawaban Anda telah kami terima. Feedback Anda sangat berarti untuk membantu kami meningkatkan layanan.</p>
  <div class="ty-meta">
    <span>Survey: <strong>{{ $survey->title }}</strong></span>
    <span>Dikirim: <strong>{{ now()->format('d M Y, H:i') }}</strong></span>
  </div>
</div>
@endsection
