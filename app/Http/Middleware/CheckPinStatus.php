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

        $hasPin    = !empty($user->pin);
        $isVerified = session('pin_verified', false);

        Log::info('PIN Status Check for User: ' . $user->full_name, [
            'user_id'       => $user->id,
            'has_pin'       => $hasPin    ? 'YES' : 'NO',
            'is_verified'   => $isVerified ? 'YES' : 'NO',
            'current_route' => $request->path(),
        ]);

        // 2️⃣ Exempt PIN-related routes to prevent infinite loop
        if ($request->is('verify_pin', 'update_pin')) {
            return $next($request);
        }

        // 3️⃣ If user has PIN AND it's verified — allow through with no-cache headers
        if ($hasPin && $isVerified) {
            session()->forget(['show_pin_modal', 'pin_mode']);
            return $this->withNoCache($next($request));
        }

        // 4️⃣ If user needs PIN setup/verification — still allow through but show modal
        if (!$hasPin) {
            session(['show_pin_modal' => true, 'pin_mode' => 'set']);
            Log::info('🚨 PIN Modal Triggered: SET MODE', ['user_id' => $user->id]);
        } elseif ($hasPin && !$isVerified) {
            session(['show_pin_modal' => true, 'pin_mode' => 'verify']);
            Log::info('🚨 PIN Modal Triggered: VERIFY MODE', ['user_id' => $user->id]);
        }

        return $this->withNoCache($next($request));
    }

    /**
     * ✅ Attach no-cache headers to every authenticated response
     * This prevents the browser back button from showing cached pages after logout.
     */
    private function withNoCache(Response $response): Response
    {
        return $response
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
