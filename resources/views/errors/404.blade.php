<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','ui-sans-serif','system-ui']},colors:{brand:{500:'#6366f1',600:'#4f46e5',700:'#4338ca'},violet:{500:'#8b5cf6',600:'#7c3aed'}},backgroundImage:{'gradient-brand':'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)'}}}}</script>
    <style>.bg-dot{background-image:radial-gradient(rgb(99 102 241 / 0.18) 1px,transparent 1px);background-size:20px 20px}</style>
</head>
<body class="font-sans bg-slate-950 text-white antialiased flex flex-col min-h-screen">
    <header class="px-5 md:px-8 py-5">
        <a href="/" class="inline-flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-lg bg-gradient-brand text-white font-extrabold flex items-center justify-center shadow-lg shadow-brand-600/30">c</span>
            <span class="font-extrabold text-lg tracking-tight">crmoffice</span>
        </a>
    </header>
    <main class="relative flex-1 flex items-center justify-center px-5 overflow-hidden">
        <div class="absolute inset-0 bg-dot opacity-30"></div>
        <div class="absolute -top-32 -left-32 w-[420px] h-[420px] rounded-full bg-brand-600/30 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-32 w-[420px] h-[420px] rounded-full bg-violet-600/30 blur-3xl"></div>
        <div class="relative text-center max-w-2xl py-12">
            <div class="text-7xl md:text-8xl mb-6">&#128269;</div>
            <div class="inline-flex items-center gap-2 text-xs font-bold tracking-wider uppercase text-brand-200 bg-white/5 border border-white/10 rounded-full px-3 py-1.5 mb-8">Error &middot; 404</div>
            <h1 class="text-5xl md:text-6xl font-extrabold leading-tight tracking-tight mb-4">Halaman Tidak Ditemukan</h1>
            <p class="text-slate-300 text-base md:text-lg leading-relaxed max-w-md mx-auto">URL yang kamu cari sudah pindah, salah ketik, atau tidak pernah ada. Cek lagi alamatnya atau kembali ke beranda.</p>
            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                <a href="/" class="inline-flex items-center gap-2 text-base font-semibold bg-white text-slate-900 px-6 py-3.5 rounded-xl hover:bg-slate-100 transition shadow-xl">&#8592; Kembali</a>
                <a href="/docs" class="inline-flex items-center gap-2 text-base font-semibold bg-white/10 border border-white/20 text-white px-6 py-3.5 rounded-xl hover:bg-white/15 transition">Dokumentasi</a>
            </div>
        </div>
    </main>
    <footer class="px-5 md:px-8 py-5 text-center text-xs text-slate-500">&copy; {{ date('Y') }} crmoffice</footer>
</body>
</html>
