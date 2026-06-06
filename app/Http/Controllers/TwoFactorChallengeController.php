<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function show(): View
    {
        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request, TwoFactorService $tfa): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        if (! $user) {
            return redirect()->route('home');
        }

        $code = preg_replace('/\s+/', '', $data['code']);

        if ($tfa->confirm($user, $code) || $tfa->consumeRecoveryCode($user, $code)) {
            $request->session()->put('two_factor_passed', true);

            return redirect()->intended('/admin');
        }

        throw ValidationException::withMessages(['code' => ['Invalid TOTP or recovery code.']]);
    }
}
