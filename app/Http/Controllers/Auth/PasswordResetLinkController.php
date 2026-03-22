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

        // ✅ Hanapin ang user sa email
        $user = User::where('email', $request->email)->first();

        // ✅ Kung hindi mahanap ang email — magpakita ng error
        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No account found with this email address.']);
        }

        // ✅ I-reset ang password pabalik sa default 'password123'
        $user->password = Hash::make('password123');
        $user->save();

        // ✅ I-redirect pabalik sa login na may success message
        return redirect()->route('login')
            ->with('status', 'Password has been reset to default (password123). Please login and change your password.');
    }
}
