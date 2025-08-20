<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasWallet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next) {
        $user = $request->user();
        if (!$user?->has_wallet) {
            return response()->json(['message' => 'Wallet required for this action.'], 403);
        }
        return $next($request);
    }
}
