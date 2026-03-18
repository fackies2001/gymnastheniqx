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
use Illuminate\Support\Str; // ✅ IDAGDAG

class AuthenticatedSessionController extends Controller
{
    protected $employeeServices;

    public function __construct(EmployeeService $employeeServices)
    {
        $this->employeeServices = $employeeServices;
    }

    public function create(): \Illuminate\Http\Response
    {
        return response(view('auth.login'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = auth()->user();

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

        $user->tokens()->where('name', 'datatable')->delete();
        $token = $user->createToken('datatable')->plainTextToken;
        session(['sanctum_token' => $token]);

        // ✅ SINGLE DEVICE LOGIN — generate new session token
        $sessionToken = Str::random(60);
        $user->update(['session_token' => $sessionToken]);
        session(['session_token' => $sessionToken]);

        // ✅ CHECK PIN STATUS (hindi binago)
        $hasPin = !empty($user->pin);

        if (!$hasPin) {
            session([
                'show_pin_modal' => true,
                'pin_verified'   => false,
                'pin_mode'       => 'set'
            ]);
        } else {
            session([
                'show_pin_modal' => true,
                'pin_verified'   => false,
                'pin_mode'       => 'verify'
            ]);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // ✅ CLEAR session token sa DB pag nag-logout
        if ($user) {
            $user->update(['session_token' => null]);
        }

        session()->forget(['pin_verified', 'show_pin_modal', 'pin_mode', 'sanctum_token', 'session_token']);

        Log::info('User Logged Out', [
            'user_id' => auth()->id(),
            'time'    => now(),
            'ip'      => $request->ip(),
        ]);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
