<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    protected $employeeServices;

    public function __construct(EmployeeService $employeeServices)
    {
        $this->employeeServices = $employeeServices;
    }

    /**
     * Display the login view.
     */
    public function create(): \Illuminate\Http\Response
    {
        // ✅ NO-CACHE HEADERS — prevents browser back button after logout
        return response(view('auth.login'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = auth()->user();

        // ✅ REGENERATE SESSION FIRST
        $request->session()->regenerate();

        Log::info('User Logged In: ', [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'time'       => now(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status'     => 'success',
            'message'    => 'User logged in successfully.',
            'has_pin'    => !empty($user->pin) ? 'YES' : 'NO',
        ]);

        // Delete old token for this device
        $user->tokens()->where('name', 'datatable')->delete();

        $token = $user->createToken('datatable')->plainTextToken;
        session(['sanctum_token' => $token]);

        // ✅ CHECK PIN STATUS
        $hasPin = !empty($user->pin);

        if (!$hasPin) {
            session([
                'show_pin_modal' => true,
                'pin_verified'   => false,
                'pin_mode'       => 'set'
            ]);

            Log::info('🆕 PIN Modal: NEW USER - Set PIN required', [
                'user_id'      => $user->id,
                'session_data' => session()->all()
            ]);
        } else {
            session([
                'show_pin_modal' => true,
                'pin_verified'   => false,
                'pin_mode'       => 'verify'
            ]);

            Log::info('🔐 PIN Modal: EXISTING USER - Verify PIN required', [
                'user_id'      => $user->id,
                'session_data' => session()->all()
            ]);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // ✅ Clear PIN session data
        session()->forget(['pin_verified', 'show_pin_modal', 'pin_mode', 'sanctum_token']);

        Log::info('User Logged Out', [
            'user_id' => auth()->id(),
            'time'    => now(),
            'ip'      => $request->ip(),
        ]);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ✅ Redirect to login — no-cache handled by create() and CheckPinStatus
        return redirect('/login');
    }
}
