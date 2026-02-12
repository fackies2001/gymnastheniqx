<?php

/*
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\EmployeeService;
use Log;

// class AuthenticatedSessionController extends Controller
// {
//     /**
//      * Display the login view.
//      */

//     protected $employeeServices;

//     public function __construct(EmployeeService $employeeServices)
//     {
//         $this->employeeServices = $employeeServices;
//     }

//     public function create(): View
//     {
//         return view('auth.login');
//     }

//     /**
//      * Handle an incoming authentication request.
//      */
//     public function store(LoginRequest $request): RedirectResponse
//     {
//         $request->authenticate();

//         // $get_auth_employee = $this->employeeServices->get_auth_employees($request->email)->first();
//         $user = auth()->user();


//         Log::info(
//             'User Logged In: ',
//             [
//                 'user_id' => $user->id,
//                 'email' => $user->email,
//                 'time' => now(),
//                 'ip' => $request->ip(),
//                 'user_agent' => $request->userAgent(),
//                 'status' => 'success',
//                 'message' => 'User logged in successfully.',
//                 'pincode' => $user->pincode,
//             ]
//         );
//         session(['user_pincode' => $user->pincode]);
//         // Delete old token for this device
//         $user->tokens()->where('name', 'datatable')->delete();

//         $token = $user->createToken('datatable')->plainTextToken;
//         // dd($get_auth_employee);
//         session(['sanctum_token' => $token]);

//         $request->session()->regenerate();

//         // ✅ CHECK PIN STATUS AFTER LOGIN
//         $employee = $user->employee;
//         if ($employee) {
//             $hasPin = !empty($employee->pin);

//             if (!$hasPin) {
//                 // New user, need to set PIN
//                 session(['show_pin_modal' => true, 'pin_verified' => false]);
//             } elseif (!session('pin_verified')) {
//                 // Existing user, need to verify PIN
//                 session(['show_pin_modal' => true, 'pin_verified' => false]);
//             }
//         }

//         return redirect()->intended(route('dashboard', absolute: false));
//     }

//     /**
//      * Destroy an authenticated session.
//      */

//     public function destroy(Request $request): RedirectResponse
//     {
//         Auth::guard('web')->logout();

//         $request->session()->invalidate();
//         $request->session()->regenerateToken();

//         // ✅ CLEAR PIN SESSION
//         session()->forget(['pin_verified', 'show_pin_modal']);

//         return redirect('/login');
//     }
// }
