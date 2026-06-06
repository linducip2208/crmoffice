<x-mail::message>
# Welcome to {{ config('app.name') }}, {{ $name }}!

Your account has been activated. Here is your login information:

<x-mail::panel>
**Email:** {{ $email }}<br>
**Password:** {{ $password }}
</x-mail::panel>

<x-mail::button :url="route('filament.admin.auth.login')">
Go to Dashboard
</x-mail::button>

Please change your password after your first login.

{{ config('app.name') }}
</x-mail::message>
