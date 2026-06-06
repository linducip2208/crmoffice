<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $contact = $request->user('portal');

        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'auth' => [
                'contact' => $contact ? [
                    'id' => $contact->id,
                    'name' => trim(($contact->first_name ?? '').' '.($contact->last_name ?? '')),
                    'email' => $contact->email,
                    'client' => $contact->client ? [
                        'id' => $contact->client->id,
                        'company_name' => $contact->client->company_name,
                    ] : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'ziggy' => null,
        ]);
    }
}
