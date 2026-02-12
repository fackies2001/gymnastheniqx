<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\EmployeeService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Log;

class SPAController extends Controller
{
    // SPA login
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 1. Get user by email
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // 2. Optional: log login info
        Log::info('User Logged In: ', [
            'user_id' => $user->id,
            'email' => $user->email,
            'time' => now(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'success',
            'message' => 'User logged in successfully.',
            'pincode' => $user->pincode ?? null,
        ]);

        // 3. Delete old token for this device (optional)
        $user->tokens()->where('name', 'datatable')->delete();

        // 4. Create a new Sanctum token
        $token = $user->createToken('datatable')->plainTextToken;

        // 5. Return JSON to SPA
        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'authenticated' => (bool) $user,
            'user' => $user,
        ], 200); // ALWAYS 200
    }

    // SPA logout
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
