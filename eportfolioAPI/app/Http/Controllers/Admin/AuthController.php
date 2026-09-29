<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Audit post-mongo Bug 3 (2026-09-28): the role check is part of the
        // credential query, not a post-attempt branch. The previous flow
        // attempt() → check is_admin → logout + distinct "You do not have
        // admin access" error confirmed password validity to anyone probing
        // /admin/login with a valid non-admin account (free credential
        // oracle). Folding 'is_admin' into attempt() makes wrong-role take
        // the identical generic-failure path — note the boolean: Mongo
        // compares strictly, and the stored flag is a real bool, so int 1
        // would never match (verified live).
        if (Auth::attempt($credentials + ['is_admin' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
