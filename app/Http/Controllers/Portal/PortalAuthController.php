<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PortalAuthController extends Controller
{
    public function showAccept(string $token): Response
    {
        $contact = Contact::query()
            ->where('invitation_token', $token)
            ->where(function ($q) {
                $q->whereNull('invitation_expires_at')
                    ->orWhere('invitation_expires_at', '>', now());
            })
            ->first();

        if (! $contact) {
            return Inertia::render('Portal/InvalidInvitation');
        }

        return Inertia::render('Portal/AcceptInvitation', [
            'contact' => [
                'id' => $contact->id,
                'email' => $contact->email,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
            ],
            'token' => $token,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $contact = Contact::query()
            ->where('invitation_token', $token)
            ->where(function ($q) {
                $q->whereNull('invitation_expires_at')
                    ->orWhere('invitation_expires_at', '>', now());
            })
            ->firstOrFail();

        $contact->update([
            'password' => Hash::make($data['password']),
            'portal_access' => true,
            'invitation_token' => null,
            'invitation_expires_at' => null,
        ]);

        Auth::guard('portal')->login($contact);

        return redirect()->route('portal.home');
    }

    public function showLogin(): Response
    {
        return Inertia::render('Portal/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::guard('portal')->attempt($data, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah, atau portal access belum diaktifkan.',
            ]);
        }

        $contact = Auth::guard('portal')->user();
        if (! $contact->portal_access) {
            Auth::guard('portal')->logout();
            throw ValidationException::withMessages([
                'email' => 'Portal access dinonaktifkan untuk akun ini.',
            ]);
        }

        $contact->update(['last_login_at' => now()]);

        return redirect()->intended(route('portal.home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('portal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
