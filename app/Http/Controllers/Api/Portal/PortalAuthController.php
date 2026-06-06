<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PortalAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string|max:120',
        ]);

        $contact = Contact::where('email', $data['email'])->first();
        if (! $contact || ! $contact->portal_access || ! $contact->password || ! Hash::check($data['password'], $contact->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials or portal access disabled.']]);
        }

        $contact->update(['last_login_at' => now()]);

        $token = $contact->createToken($data['device_name'] ?? 'portal-mobile', ['portal'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'contact' => $this->profile($contact),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->profile($request->user())]);
    }

    private function profile(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'full_name' => trim($contact->first_name.' '.$contact->last_name),
            'email' => $contact->email,
            'locale' => $contact->locale,
            'client' => $contact->client ? [
                'id' => $contact->client->id,
                'company_name' => $contact->client->company_name,
            ] : null,
        ];
    }
}
