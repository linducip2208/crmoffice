<x-filament-panels::page>
    <div class="space-y-6 max-w-3xl">

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h3 class="text-lg font-semibold mb-4">Profile Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input wire:model="name" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input wire:model="email" type="email" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input wire:model="phone" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Locale</label>
                    <select wire:model="locale" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800"><option value="id">Indonesia</option><option value="en">English</option></select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Timezone</label>
                    <input wire:model="timezone" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                </div>
            </div>
            <button wire:click="saveProfile" class="mt-4 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-semibold">Save Profile</button>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h3 class="text-lg font-semibold mb-4">Change Password</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Current Password</label>
                    <input wire:model="currentPassword" type="password" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">New Password (min 8 chars)</label>
                    <input wire:model="newPassword" type="password" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Confirm New Password</label>
                    <input wire:model="newPasswordConfirmation" type="password" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                </div>
            </div>
            <button wire:click="changePassword" class="mt-4 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-semibold">Change Password</button>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h3 class="text-lg font-semibold mb-4">Two-Factor Authentication</h3>

            @if($twoFactorEnabled)
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 text-green-800 px-3 py-1 text-xs font-semibold">✓ 2FA Enabled</span>
                </div>
                <button wire:click="disable2Fa" wire:confirm="Disable 2FA? Your account will be less secure." class="rounded-md bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-semibold">Disable 2FA</button>

            @elseif($twoFactorSecret)
                <p class="mb-3 text-sm text-gray-700 dark:text-gray-300">Scan this QR with Google Authenticator / Authy / 1Password:</p>
                <img src="{{ $twoFactorQrSvg }}" alt="QR" class="rounded border border-gray-200 dark:border-gray-700 mb-3" style="max-width:200px">
                <p class="text-xs text-gray-500 mb-3">Or enter manually: <code class="font-mono bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">{{ $twoFactorSecret }}</code></p>
                <div class="flex gap-2 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-1">Enter 6-digit code</label>
                        <input wire:model="totpCode" maxlength="6" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 font-mono">
                    </div>
                    <button wire:click="confirm2Fa" class="rounded-md bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-sm font-semibold">Confirm &amp; Enable</button>
                </div>

            @else
                <p class="mb-4 text-sm text-gray-700 dark:text-gray-300">Add extra security with TOTP (Time-based One-Time Password).</p>
                <button wire:click="start2FaSetup" class="rounded-md bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-semibold">Enable 2FA</button>
            @endif
        </div>

    </div>
</x-filament-panels::page>
