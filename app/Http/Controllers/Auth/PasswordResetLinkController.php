<?php

// ============================================================
// FILE: app/Http/Controllers/Auth/PasswordResetLinkController.php
// PALITAN ANG BUONG FILE NG CODE NA ITO
// ============================================================

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * ✅ UPDATED: Instead of sending email reset link,
     *    directly reset password to default 'password123'
     *    Para sa dummy/fake emails na hindi totoo
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // ✅ Hindi registered sa system
        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No account found with this email address.']);
        }

        // ✅ Registered — sabihin na kontakin ang admin
        return back()->with('status', 'Account found! Please contact your Admin to reset your password.');
    }
}
