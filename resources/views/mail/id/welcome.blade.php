<x-mail::message>
# Selamat Datang di {{ config('app.name') }}, {{ $name }}!

Akun Anda telah aktif. Berikut informasi login Anda:

<x-mail::panel>
**Email:** {{ $email }}<br>
**Password:** {{ $password }}
</x-mail::panel>

<x-mail::button :url="route('filament.admin.auth.login')">
Masuk ke Dashboard
</x-mail::button>

Segera ganti password Anda setelah login pertama.

{{ config('app.name') }}
</x-mail::message>
