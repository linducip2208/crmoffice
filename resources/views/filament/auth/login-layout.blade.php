@php
    $appName = $appName ?? config('app.name', 'crmoffice');
    $demoAccounts = $demoAccounts ?? [];
@endphp

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Admin — {{ $appName }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700&display=swap" rel="stylesheet">
    @filamentStyles
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ['Inter', 'ui-sans-serif', 'system-ui'],
              mono: ['JetBrains Mono', 'ui-monospace', 'SFMono-Regular'],
            },
          }
        }
      }
    </script>
    <style>
        .fi-simple-layout, .fi-simple-main-ctn, .fi-simple-main { display: contents; }
        .fi-simple-main { width: 100%; max-width: 100% !important; padding: 0 !important; }
        .fi-layout-base { display: contents; }
        body { margin: 0; background: #f8fafc; }
    </style>
</head>
<body class="font-sans bg-slate-50 text-slate-900 antialiased min-h-screen flex flex-col">

<header class="sticky top-0 z-50 bg-white/80 border-b border-slate-200/70" style="backdrop-filter: blur(12px) saturate(180%);">
  <div class="max-w-7xl mx-auto px-5 md:px-8 h-16 flex items-center justify-between">
    <a href="/" class="flex items-center gap-2.5">
      <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-600 to-blue-800 text-white font-extrabold flex items-center justify-center shadow-md shadow-blue-600/30">c</span>
      <span class="font-extrabold text-lg tracking-tight">{{ $appName }}</span>
    </a>
    <div class="flex items-center gap-2">
      <a href="/portal" class="hidden sm:inline-flex text-sm font-medium text-slate-700 hover:text-slate-900 px-3 py-2">Portal Klien</a>
    </div>
  </div>
</header>

<main class="flex-1">
    <div class="min-h-[calc(100vh-200px)] grid lg:grid-cols-2 gap-0">
        {{-- Left: hero brand panel --}}
        <div class="hidden lg:flex relative bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 p-12 flex-col justify-between overflow-hidden">
            <div class="absolute inset-0 opacity-30"
                 style="background-image: radial-gradient(circle at 30% 20%, rgba(59,130,246,.4), transparent 50%), radial-gradient(circle at 70% 80%, rgba(37,99,235,.3), transparent 50%);"></div>
            <div class="absolute -bottom-20 -right-20 text-[20rem] opacity-10">&#x1F4BC;</div>

            <div class="relative">
                <a href="/" class="flex items-center gap-2 text-white">
                    <span class="w-9 h-9 rounded-lg bg-white/20 backdrop-blur text-white font-extrabold flex items-center justify-center shadow-md">c</span>
                    <span class="font-extrabold text-2xl tracking-tight">{{ $appName }}</span>
                </a>
            </div>

            <div class="relative text-white">
                <h2 class="text-5xl font-bold leading-tight mb-4">Kelola Bisnis Tanpa Batas &#x1F680;</h2>
                <p class="text-blue-100 text-lg leading-relaxed mb-8 max-w-md">CRM self-hosted lengkap untuk agency, freelancer, dan SMB. Clients, leads, invoices, projects, tickets — semua dalam satu dashboard.</p>
                <div class="grid grid-cols-3 gap-4 max-w-md">
                    <div class="bg-white/10 backdrop-blur p-4 rounded-2xl">
                        <div class="text-3xl mb-1">&#x1F4BC;</div>
                        <div class="text-xs font-medium">CRM Lengkap</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur p-4 rounded-2xl">
                        <div class="text-3xl mb-1">&#x26A1;</div>
                        <div class="text-xs font-medium">Self-Hosted</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur p-4 rounded-2xl">
                        <div class="text-3xl mb-1">&#x1F50C;</div>
                        <div class="text-xs font-medium">BYO Integrasi</div>
                    </div>
                </div>
            </div>

            <div class="relative text-blue-200/50 text-xs">
                &copy; {{ date('Y') }} {{ $appName }} &middot; Powered by Laravel
            </div>
        </div>

        {{-- Right: login form --}}
        <div class="flex items-center justify-center p-8 lg:p-16">
            <div class="w-full max-w-md">
                <h1 class="text-4xl font-bold text-slate-900 mb-2">Masuk Admin</h1>
                <p class="text-slate-500 mb-8">Masuk ke panel administrasi {{ $appName }}.</p>

                {{-- Filament form renders here via $slot --}}
                <div class="filament-form-wrapper">
                    {{ $slot }}
                </div>

                <div class="my-8 flex items-center gap-3">
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <span class="text-xs text-slate-400 uppercase tracking-wider">atau gunakan</span>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>

                @if (!empty($demoAccounts))
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm">
                    <div class="font-semibold text-slate-800 mb-2">&#x1F9EA; Demo Login</div>
                    <div class="space-y-1 text-slate-600 text-xs font-mono">
                        @foreach ($demoAccounts as $account)
                            <div><span class="font-bold">{{ $account['role'] }}:</span> {{ $account['email'] }} / {{ $account['password'] }}</div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>

<footer class="bg-slate-950 text-slate-400 border-t border-slate-800">
  <div class="max-w-7xl mx-auto px-5 md:px-8 py-6">
    <div class="flex flex-wrap items-center justify-between gap-4 text-xs">
      <div>&copy; {{ date('Y') }} {{ $appName }}. Released under MIT License.</div>
      <div>
        <a href="/" class="hover:text-white transition">Beranda</a>
      </div>
    </div>
  </div>
</footer>

@filamentScripts
</body>
</html>
