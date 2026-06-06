@php
    $appName = config('app.name', 'crmoffice');
    $demoAccounts = [
        ['role' => 'Owner',      'email' => 'owner@crmoffice.local',      'password' => 'password', 'scope' => 'Akses penuh: roles, providers, billing, audit log'],
        ['role' => 'Admin',      'email' => 'admin@crmoffice.local',      'password' => 'password', 'scope' => 'Semua modul kecuali manage roles & reveal secret'],
        ['role' => 'Sales',      'email' => 'sales@crmoffice.local',      'password' => 'password', 'scope' => 'Leads, clients, estimates, proposals, contracts'],
        ['role' => 'Project Mgr','email' => 'pm@crmoffice.local',         'password' => 'password', 'scope' => 'Projects, milestones, tasks, time entries, gantt'],
        ['role' => 'Support',    'email' => 'support@crmoffice.local',    'password' => 'password', 'scope' => 'Tickets, SLA, knowledge base, departments'],
        ['role' => 'Accountant', 'email' => 'accountant@crmoffice.local', 'password' => 'password', 'scope' => 'Invoices, payments, credit notes, expenses, reports'],
        ['role' => 'Staff',      'email' => 'staff@crmoffice.local',      'password' => 'password', 'scope' => 'Assigned tasks + own time entries'],
    ];
@endphp

@extends('marketing._layout', ['title' => 'Masuk — ' . $appName])

@section('content')
<div class="min-h-[calc(100vh-200px)] grid lg:grid-cols-2 gap-0 -mx-4">
    {{-- Left: hero brand panel --}}
    <div class="hidden lg:flex relative bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 p-12 flex-col justify-between overflow-hidden">
        <div class="absolute inset-0 opacity-30"
             style="background-image: radial-gradient(circle at 30% 20%, rgba(59,130,246,.4), transparent 50%), radial-gradient(circle at 70% 80%, rgba(37,99,235,.3), transparent 50%);"></div>
        <div class="absolute -bottom-20 -right-20 text-[20rem] opacity-10">💼</div>

        <div class="relative">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-white">
                <span class="w-9 h-9 rounded-lg bg-white/20 backdrop-blur text-white font-extrabold flex items-center justify-center shadow-md">c</span>
                <span class="font-extrabold text-2xl tracking-tight">{{ $appName }}</span>
            </a>
        </div>

        <div class="relative text-white">
            <h2 class="text-5xl font-bold leading-tight mb-4">Kelola Bisnis Tanpa Batas 🚀</h2>
            <p class="text-blue-100 text-lg leading-relaxed mb-8 max-w-md">CRM self-hosted lengkap untuk agency, freelancer, dan SMB. Clients, leads, invoices, projects, tickets — semua dalam satu dashboard.</p>
            <div class="grid grid-cols-3 gap-4 max-w-md">
                <div class="bg-white/10 backdrop-blur p-4 rounded-2xl">
                    <div class="text-3xl mb-1">💼</div>
                    <div class="text-xs font-medium">CRM Lengkap</div>
                </div>
                <div class="bg-white/10 backdrop-blur p-4 rounded-2xl">
                    <div class="text-3xl mb-1">⚡</div>
                    <div class="text-xs font-medium">Self-Hosted</div>
                </div>
                <div class="bg-white/10 backdrop-blur p-4 rounded-2xl">
                    <div class="text-3xl mb-1">🔌</div>
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
            <h1 class="text-4xl font-bold text-slate-900 mb-2">Masuk</h1>
            <p class="text-slate-500 mb-8">Masuk ke dashboard {{ $appName }}.</p>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    @foreach ($errors->all() as $err)
                        <div>&#9888;&#65039; {{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="email@example.com"
                           class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <input type="password" name="password" required
                           placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                           class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition outline-none">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Ingatkan saya
                    </label>
                    <a href="/admin/password/reset" class="text-sm text-blue-600 font-semibold hover:underline">Lupa password?</a>
                </div>
                <button type="submit" class="w-full py-3.5 bg-gradient-to-br from-blue-500 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/50 active:scale-[0.98] transition">
                    Masuk &rarr;
                </button>
            </form>

            <div class="my-8 flex items-center gap-3">
                <div class="flex-1 h-px bg-slate-200"></div>
                <span class="text-xs text-slate-400 uppercase tracking-wider">atau</span>
                <div class="flex-1 h-px bg-slate-200"></div>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm">
                <div class="font-semibold text-slate-800 mb-2">&#x1F9EA; Demo Login</div>
                <div class="space-y-1 text-slate-600 text-xs font-mono">
                    @foreach ($demoAccounts as $account)
                        <div><span class="font-bold">{{ $account['role'] }}:</span> {{ $account['email'] }} / {{ $account['password'] }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
