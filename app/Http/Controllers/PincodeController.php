<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PincodeController extends Controller
{
    /**
     * Verify or Set PIN
     */
    public function verify(Request $request)
    {
        $request->validate([
            'pin' => 'required|array|size:4',
            'pin.*' => 'required|numeric|digits:1'
        ]);

        $pin = implode('', $request->pin);
        $user = Auth::user();

        // ✅ Case 1: User has NO PIN yet - SET IT
        if (empty($user->pin)) {
            $user->pin = Hash::make($pin);
            $user->save();

            session(['pin_verified' => true]);

            return response()->json([
                'success' => true,
                'message' => 'PIN created successfully! Welcome to the dashboard.'
            ]);
        }

        // ✅ Case 2: User has PIN - VERIFY IT
        if (Hash::check($pin, $user->pin)) {
            session(['pin_verified' => true]);

            return response()->json([
                'success' => true,
                'message' => 'PIN verified successfully!'
            ]);
        }

        // ❌ Wrong PIN
        return response()->json([
            'success' => false,
            'message' => 'Incorrect PIN. Please try again.'
        ], 422);
    }

    /**
     * Optional: Separate SET method if needed
     */
    public function set(Request $request)
    {
        $request->validate([
            'pin' => 'required|array|size:4',
            'pin.*' => 'required|numeric|digits:1'
        ]);

        $pin = implode('', $request->pin);
        $user = Auth::user();

        $user->pin = Hash::make($pin);
        $user->save();

        session(['pin_verified' => true]);

        return response()->json([
            'success' => true,
            'message' => 'PIN set successfully!'
        ]);
    }
}
