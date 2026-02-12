<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckPinStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1️⃣ Skip if not logged in (Guest)
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // 2️⃣ Check PIN directly from $user
        $hasPin = !empty($user->pin);
        $isVerified = session('pin_verified', false);

        // 🔍 DEBUG LOG
        Log::info('PIN Status Check for User: ' . $user->full_name, [
            'user_id' => $user->id,
            'has_pin' => $hasPin ? 'YES' : 'NO',
            'is_verified' => $isVerified ? 'YES' : 'NO',
            'current_route' => $request->path(),
        ]);

        // 3️⃣ EXEMPT PIN-related routes to prevent infinite loop
        if ($request->is('verify_pin', 'update_pin')) {
            return $next($request);
        }

        // 4️⃣ If user has PIN AND it's verified, allow access
        if ($hasPin && $isVerified) {
            session()->forget(['show_pin_modal', 'pin_mode']);
            return $next($request);
        }

        // 5️⃣ If user needs PIN setup/verification
        if (!$hasPin) {
            // User has NO PIN - must set
            session(['show_pin_modal' => true, 'pin_mode' => 'set']);
            Log::info('🚨 PIN Modal Triggered: SET MODE', ['user_id' => $user->id]);
        } elseif ($hasPin && !$isVerified) {
            // User HAS PIN but NOT verified - must verify
            session(['show_pin_modal' => true, 'pin_mode' => 'verify']);
            Log::info('🚨 PIN Modal Triggered: VERIFY MODE', ['user_id' => $user->id]);
        }

        return $next($request);
    }
}
