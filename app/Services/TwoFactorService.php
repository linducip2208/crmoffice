<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    public function __construct(private Google2FA $google2fa) {}

    public function enable(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::random(10).'-'.Str::random(10))->all();

        $user->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
        ])->save();

        return [
            'secret' => $secret,
            'qr_url' => $this->google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            ),
            'recovery_codes' => $recoveryCodes,
        ];
    }

    public function confirm(User $user, string $code): bool
    {
        $secret = $this->decryptedSecret($user);
        if (! $secret) {
            return false;
        }

        return (bool) $this->google2fa->verifyKey($secret, $code);
    }

    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ])->save();
    }

    public function isEnabled(User $user): bool
    {
        return ! empty($user->two_factor_secret);
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        if (empty($user->two_factor_recovery_codes)) {
            return false;
        }

        $codes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true) ?: [];
        $idx = array_search($code, $codes, true);
        if ($idx === false) {
            return false;
        }

        unset($codes[$idx]);
        $user->forceFill([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($codes))),
        ])->save();

        return true;
    }

    private function decryptedSecret(User $user): ?string
    {
        if (! $user->two_factor_secret) {
            return null;
        }

        try {
            return Crypt::decryptString($user->two_factor_secret);
        } catch (\Throwable) {
            return null;
        }
    }
}
