<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects authenticated admin users with 2FA enabled to the challenge page
 * unless they've completed it for this session.
 */
class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user
            && ! empty($user->two_factor_secret)
            && ! $request->session()->get('two_factor_passed')
            && ! $request->routeIs('two-factor.*', 'logout', 'filament.admin.auth.*')
        ) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
