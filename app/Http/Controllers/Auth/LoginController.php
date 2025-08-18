<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        // Rate limit check (same as Livewire)
        $request->ensureIsNotRateLimited();

        $credentials = $request->only('email', 'password');
        $remember    = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            // count the miss, then return same auth.failed message
            $request->hitThrottle();

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // success: clear limiter + regenerate session
        $request->clearThrottle();
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function destroy()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/'); // or route('login')
    }
}
