<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\Mail\AccountedCreated;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\Role;
use App\Services\NotificationService;
use Illuminate\Validation\Rules\Password;

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
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:employee,email',
                'regex:/^[a-zA-Z0-9._%+\-]+@gmail\.com$/',
            ],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ], [
            'email.regex' => 'Only Gmail addresses are allowed (e.g. example@gmail.com).',
        ]);

        $roleId = Role::query()->where('role_name', 'requestor')->value('id')
            ?? Role::query()->value('id');

        $user = User::create([
            'full_name' => $request->full_name,
            'username' => strtolower(str_replace(' ', '.', $request->full_name)) . rand(100, 999),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $roleId,
            'status' => 'Active',
        ]);

        $token = Password::createToken($user);

        $user->notify(new AccountedCreated($token));

        $messageData = [
            'user_id' => $user->id,
            'full_name' => $user->full_name,
            'profile_photo' => $user->profile_photo,
            'message' => "Welcome {$user->full_name}! Your account has been created.",
            'icon' => 'fas fa-user-plus',
        ];

        $notifier->notifyUserCreated($user, $messageData);

        event(new Registered($user));

        return redirect(route('user.management', absolute: false));
    }
}
