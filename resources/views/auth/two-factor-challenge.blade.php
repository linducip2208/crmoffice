<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Challenge · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h1 class="text-2xl font-semibold mb-1">Two-factor authentication</h1>
        <p class="text-sm text-slate-500 mb-6">Enter the 6-digit code from your authenticator app, or a recovery code.</p>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-4">
            @csrf
            <input
                name="code"
                inputmode="numeric"
                autofocus
                autocomplete="one-time-code"
                class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-center tracking-widest text-lg"
                placeholder="123 456"
                required
            />
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg py-2.5">
                Verify
            </button>
        </form>

        <form method="POST" action="{{ url('/admin/logout') }}" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-sm text-slate-500 hover:text-slate-700">Sign out</button>
        </form>
    </div>
</body>
</html>
