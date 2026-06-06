@php
    $appName = config('app.name', 'crmoffice');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code ?? 'Error' }} — {{ $appName }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: { extend: {
          fontFamily: { sans: ['Inter','ui-sans-serif','system-ui'], mono: ['JetBrains Mono','ui-monospace'] },
          colors: { brand: { 500:'#6366f1', 600:'#4f46e5', 700:'#4338ca' }, violet: { 500:'#8b5cf6', 600:'#7c3aed' } },
          backgroundImage: { 'gradient-brand': 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)' },
        }}
      }
    </script>
    <style>.bg-dot{background-image:radial-gradient(rgb(99 102 241 / 0.18) 1px,transparent 1px);background-size:20px 20px}</style>
</head>
<body class="font-sans bg-slate-950 text-white antialiased min-h-screen flex flex-col">
  <header class="px-5 md:px-8 py-5">
    <a href="/" class="inline-flex items-center gap-2.5">
      <span class="w-9 h-9 rounded-lg bg-gradient-brand text-white font-extrabold flex items-center justify-center shadow-lg shadow-brand-600/30">c</span>
      <span class="font-extrabold text-lg tracking-tight">{{ $appName }}</span>
    </a>
  </header>

  <main class="relative flex-1 flex items-center justify-center px-5 overflow-hidden">
    <div class="absolute inset-0 bg-dot opacity-30"></div>
    <div class="absolute -top-32 -left-32 w-[420px] h-[420px] rounded-full bg-brand-600/30 blur-3xl"></div>
    <div class="absolute -bottom-32 -right-32 w-[420px] h-[420px] rounded-full bg-violet-600/30 blur-3xl"></div>

    <div class="relative text-center max-w-2xl py-12">
      <div class="inline-flex items-center gap-2 text-xs font-bold tracking-wider uppercase text-brand-200 bg-white/5 border border-white/10 rounded-full px-3 py-1.5 mb-8">
        Error · {{ $code ?? '???' }}
      </div>
      <h1 class="text-7xl md:text-9xl font-extrabold leading-none bg-gradient-to-r from-brand-300 via-violet-400 to-pink-400 bg-clip-text text-transparent tracking-tight">
        {{ $code ?? '???' }}
      </h1>
      <h2 class="mt-6 text-2xl md:text-4xl font-extrabold tracking-tight">{{ $heading ?? 'Sesuatu salah.' }}</h2>
      <p class="mt-4 text-slate-300 text-base md:text-lg leading-relaxed">{{ $message ?? 'Halaman ini tidak tersedia. Coba kembali ke beranda.' }}</p>

      <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
        <a href="/" class="inline-flex items-center gap-2 text-base font-semibold bg-white text-slate-900 px-6 py-3.5 rounded-xl hover:bg-slate-100 transition shadow-xl">
          ← Kembali ke Beranda
        </a>
        <a href="/contact" class="inline-flex items-center gap-2 text-base font-semibold bg-white/10 border border-white/20 text-white px-6 py-3.5 rounded-xl hover:bg-white/15 transition">
          Lapor Masalah
        </a>
      </div>

      @isset($detail)
        <div class="mt-10 inline-block text-left bg-black/30 border border-white/10 rounded-xl px-5 py-3 font-mono text-xs text-slate-400">
          {{ $detail }}
        </div>
      @endisset
    </div>
  </main>

  <footer class="px-5 md:px-8 py-5 text-center text-xs text-slate-500">
    © {{ date('Y') }} {{ $appName }} · <a href="/sitemap.xml" class="hover:text-white">Sitemap</a> · <a href="/api/v1/health" class="hover:text-white">Status</a>
  </footer>
</body>
</html>
