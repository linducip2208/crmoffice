<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class Profile extends Page
{
    protected string $view = 'filament.admin.pages.profile';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'My Profile';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'profile';

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $locale = 'id';
    public string $timezone = 'Asia/Jakarta';
    public array $notificationPreferences = [];
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';
    public bool $twoFactorEnabled = false;
    public ?string $twoFactorSecret = null;
    public ?string $twoFactorQrSvg = null;
    public string $totpCode = '';

    public function mount(): void
    {
        $u = auth()->user();
        $this->name = $u->name;
        $this->email = $u->email;
        $this->phone = $u->phone ?? '';
        $this->locale = $u->locale ?? 'id';
        $this->timezone = $u->timezone ?? 'Asia/Jakarta';
        $this->twoFactorEnabled = filled($u->two_factor_secret);
        $this->notificationPreferences = $u->notification_preferences ?? [];
    }

    public function saveNotificationPreferences(): void
    {
        auth()->user()->update(['notification_preferences' => $this->notificationPreferences]);

        \Filament\Notifications\Notification::make()->title('Notification preferences saved')->success()->send();
    }

    public function saveProfile(): void
    {
        $u = auth()->user();
        $u->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
        ]);

        \Filament\Notifications\Notification::make()->title('Profile updated')->success()->send();
    }

    public function changePassword(): void
    {
        $u = auth()->user();

        if (! Hash::check($this->currentPassword, $u->password)) {
            \Filament\Notifications\Notification::make()->title('Current password is wrong')->danger()->send();

            return;
        }

        if (strlen($this->newPassword) < 8 || $this->newPassword !== $this->newPasswordConfirmation) {
            \Filament\Notifications\Notification::make()->title('New password must match and be at least 8 characters')->danger()->send();

            return;
        }

        $u->update(['password' => Hash::make($this->newPassword)]);
        $this->currentPassword = $this->newPassword = $this->newPasswordConfirmation = '';

        \Filament\Notifications\Notification::make()->title('Password changed')->success()->send();
    }

    public function start2FaSetup(): void
    {
        $google2fa = new Google2FA();
        $this->twoFactorSecret = $google2fa->generateSecretKey();
        $qrUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'crmoffice'),
            auth()->user()->email,
            $this->twoFactorSecret,
        );

        // Render QR as inline SVG via google chart fallback (lightweight, no extra dep)
        $this->twoFactorQrSvg = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrUrl);
    }

    public function confirm2Fa(): void
    {
        $google2fa = new Google2FA();
        if (! $google2fa->verifyKey($this->twoFactorSecret, $this->totpCode)) {
            \Filament\Notifications\Notification::make()->title('Invalid TOTP code')->danger()->send();

            return;
        }

        $recovery = collect(range(1, 10))->map(fn () => \Illuminate\Support\Str::random(10))->all();

        auth()->user()->update([
            'two_factor_secret' => encrypt($this->twoFactorSecret),
            'two_factor_recovery_codes' => encrypt(json_encode($recovery)),
        ]);

        $this->twoFactorEnabled = true;
        $this->twoFactorSecret = null;
        $this->twoFactorQrSvg = null;
        $this->totpCode = '';

        \Filament\Notifications\Notification::make()
            ->title('2FA enabled')
            ->body('Save these recovery codes: ' . implode(', ', $recovery))
            ->success()
            ->persistent()
            ->send();
    }

    public function disable2Fa(): void
    {
        auth()->user()->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);
        $this->twoFactorEnabled = false;

        \Filament\Notifications\Notification::make()->title('2FA disabled')->warning()->send();
    }
}
