<?php

namespace App\Http\Middleware;

use App\Support\AccountAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->status === 'active') {
            return $next($request);
        }

        AccountAccess::revoke($user);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'This account has been disabled. Contact an administrator.',
            ], 403);
        }

        Auth::logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect('/login')->withErrors([
            'username' => 'This account has been disabled. Contact an administrator.',
        ]);
    }
}
