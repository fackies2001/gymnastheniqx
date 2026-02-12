<?php
/*
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckPinStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Skip kung hindi login (Guest)
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // 2. 🛡️ ADMIN BYPASS: Kung Admin ang user, tuloy agad! 
        // Palitan ang 'admin' sa actual role name mo (e.g., $user->role === 'Admin')
        $employee = $user->employee;
        if ($employee && $employee->role && strtolower($employee->role->role_name) === 'admin') {
            session()->forget('show_pin_modal');
            return $next($request);
        }

        $employee = $user->employee;

        // Safety check kung sakaling walang employee record ang user
        if (!$employee) {
            return $next($request);
        }

        $hasPin = !empty($employee->pin);
        $isVerified = session('pin_verified', false);

        // 🔍 DEBUG
        Log::info('PIN Status Check for ' . $user->name, [
            'has_pin' => $hasPin ? 'YES' : 'NO',
            'is_verified' => $isVerified ? 'YES' : 'NO',
        ]);

        // 3. LOGIC CHECK
        if ($hasPin && $isVerified) {
            session()->forget('show_pin_modal');
            return $next($request);
        }

        if (!$hasPin || !$isVerified) {
            // Exempt natin ang routes na ginagamit mismo para mag-verify/update ng PIN
            // Para hindi magkaroon ng "Infinite Loop"
            if ($request->is('verify_pin', 'update_pin')) {
                return $next($request);
            }

            session(['show_pin_modal' => true]);
        }

        return $next($request);
    }
}

27-01-2026