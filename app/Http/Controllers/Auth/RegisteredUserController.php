<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\Mail\AccountedCreated;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Services\NotificationService;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth._register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, NotificationService $notifier): RedirectResponse
    {

        $request->validate([
            'employee_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required'],
        ]);

        $user = User::create([
            'employee_id' => $request->employee_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Password::createToken($user);

        $user->notify(new AccountedCreated($token));

        $employee = $user->employee;

        $messageData = [
            'user_id' => $user->id,
            'full_name' => $user->name,
            'profile_photo' => $employee?->profile_photo ?? null,
            'message' => "Welcome {$user->name}! Your account has been created.",
            'icon' => 'fas fa-user-plus',
        ];

        $notifier->notifyUserCreated($user, $messageData);

        event(new Registered($user));

        return redirect(route('user.management', absolute: false));
    }
}
