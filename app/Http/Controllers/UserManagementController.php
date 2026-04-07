<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\Role;
use App\Models\Warehouse;

class UserManagementController extends Controller
{
    /**
     * ✅ Display User Management Page
     */
    public function index()
    {
        $employees = User::with(['role', 'warehouse', 'department'])->get();

        $roles = Role::whereIn('role_name', ['admin', 'staff', 'manager'])->get();

        $warehouses = Warehouse::all();

        $departments = \App\Models\Department::where('name', 'Operations')->get();

        return view('settings.user-management.index', compact('employees', 'roles', 'warehouses', 'departments'));
    }

    /**
     * ✅ Store New Employee
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'      => 'required|string|max:255',

            // ✅ FIX 2: Gmail only
            'email'          => [
                'required',
                'email',
                'unique:employee,email',
                'regex:/^[a-zA-Z0-9._%+\-]+@gmail\.com$/'
            ],

            'role_id'        => 'required|exists:role,id',
            'department_id'  => 'nullable|exists:department,id',
            'assigned_at'    => 'nullable|exists:warehouse,id',
            'contact_number' => 'nullable|string',
            'address'        => 'nullable|string',
            'date_hired'     => 'nullable|date',
            'status'         => 'required|in:Active,Inactive,active,inactive',
            'profile_photo'  => 'nullable|image|max:2048',
            'password'       => ['required', 'string', 'confirmed', Password::defaults()],
        ], [
            // ✅ Custom error message para mas clear sa user
            'email.regex' => 'Only Gmail addresses are allowed (e.g. example@gmail.com).',
        ]);

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')
                ->store('profile_photos', 'public');
        }

        // ✅ FIX 1: Auto-generate username para hindi mag-SQL error
        $validated['username'] = strtolower(str_replace(' ', '.', $request->full_name))
            . rand(100, 999);

        $validated['password'] = Hash::make($validated['password']);
        unset($validated['password_confirmation']);
        $validated['status']   = ucfirst(strtolower($validated['status']));

        User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully!',
        ]);
    }
    /**
     * ✅ Update Existing Employee
     */
    public function update(Request $request)
    {
        $id       = $request->input('id');
        $employee = User::findOrFail($id);

        $validated = $request->validate([
            'full_name'      => 'required|string|max:255',

            // ✅ FIX 2: Gmail only sa update din
            'email'          => [
                'required',
                'email',
                'unique:employee,email,' . $id,
                'regex:/^[a-zA-Z0-9._%+\-]+@gmail\.com$/'
            ],

            'role_id'        => 'required|exists:role,id',
            'department_id'  => 'nullable|exists:department,id',
            'assigned_at'    => 'nullable|exists:warehouse,id',
            'contact_number' => 'nullable|string',
            'address'        => 'nullable|string',
            'date_hired'     => 'nullable|date',
            'status'         => 'required|in:active,inactive',
            'profile_photo'  => 'nullable|image|max:2048',
        ], [
            'email.regex' => 'Only Gmail addresses are allowed (e.g. example@gmail.com).',
        ]);

        if ($request->filled('password') || $request->filled('password_confirmation')) {
            $request->validate([
                'password' => ['required', 'string', 'confirmed', Password::defaults()],
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_photo')) {
            if ($employee->profile_photo) {
                Storage::disk('public')->delete($employee->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')
                ->store('profile_photos', 'public');
        }

        $validated['status'] = ucfirst(strtolower($validated['status']));
        $employee->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully!',
        ]);
    }

    /**
     * ✅ Delete Employee
     */
    public function destroy($id)
    {
        if ((int) $id === (int) auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $employee = User::findOrFail($id);

        if ($employee->profile_photo) {
            Storage::disk('public')->delete($employee->profile_photo);
        }

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully!',
        ]);
    }

    /**
     * ✅ Admin Reset PIN
     */
    public function resetPin(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:employee,id',
        ]);

        $user      = User::findOrFail($request->id);
        $user->pin = null;
        $user->save();

        session()->forget(['pin_verified', 'show_pin_modal', 'pin_mode']);

        Log::info('🔓 Admin Reset PIN', [
            'admin_id'         => auth()->id(),
            'target_user_id'   => $user->id,
            'target_user_name' => $user->full_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN has been reset. User will be prompted to set a new PIN on next login.',
        ]);
    }

    /**
     * ✅ Verify PIN on login (6 digits)
     */
    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin'   => 'required|array|size:6',
            'pin.*' => 'required|numeric|digits:1',
        ]);

        $pin  = implode('', $request->pin);
        $user = auth()->user();

        if (Hash::check($pin, $user->pin)) {
            session(['pin_verified' => true]);
            session()->forget(['show_pin_modal', 'pin_mode']);

            Log::info('✅ PIN Verified Successfully', [
                'user_id'   => $user->id,
                'user_name' => $user->full_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PIN verified successfully',
            ]);
        }

        Log::warning('❌ PIN Verification Failed', [
            'user_id'   => $user->id,
            'user_name' => $user->full_name,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Incorrect PIN. Please try again.',
        ], 422);
    }

    /**
     * ✅ Update/Set PIN for first time or change (6 digits)
     */
    public function updatePin(Request $request)
    {
        $request->validate([
            'pin'   => 'required|array|size:6',
            'pin.*' => 'required|numeric|digits:1',
        ]);

        $pin  = implode('', $request->pin);
        $user = auth()->user();

        $user->pin = Hash::make($pin);
        $user->save();

        session(['pin_verified' => true]);
        session()->forget(['show_pin_modal', 'pin_mode']);

        Log::info('✅ PIN Set/Updated Successfully', [
            'user_id'   => $user->id,
            'user_name' => $user->full_name,
            'is_new_pin' => session('pin_mode') === 'set',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN saved successfully',
        ]);
    }

    /**
     * ✅ Admin Reset Password — i-reset pabalik sa 'password123'
     */

    public function resetPassword(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:employee,id',
        ]);

        $user           = User::findOrFail($request->id);
        $user->password = Hash::make('password123');
        $user->save();

        Log::info('🔑 Admin Reset Password', [
            'admin_id'         => auth()->id(),
            'target_user_id'   => $user->id,
            'target_user_name' => $user->full_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password of ' . $user->full_name . ' has been reset to password123.',
        ]);
    }
}
