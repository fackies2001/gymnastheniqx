<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            'email'          => 'required|email|unique:employee,email',
            // ✅ FIX: Removed username validation — username no longer required
            'role_id'        => 'required|exists:role,id',
            'department_id'  => 'nullable|exists:department,id',
            'assigned_at'    => 'nullable|exists:warehouse,id',
            'contact_number' => 'nullable|string',
            'address'        => 'nullable|string',
            'date_hired'     => 'nullable|date',
            'status'         => 'required|in:Active,Inactive,active,inactive',
            'profile_photo'  => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')
                ->store('profile_photos', 'public');
        }

        $validated['password'] = Hash::make('password123');
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
            'email'          => 'required|email|unique:employee,email,' . $id,
            // ✅ FIX: Removed username validation — username no longer required
            'role_id'        => 'required|exists:role,id',
            'department_id'  => 'nullable|exists:department,id',
            'assigned_at'    => 'nullable|exists:warehouse,id',
            'contact_number' => 'nullable|string',
            'address'        => 'nullable|string',
            'date_hired'     => 'nullable|date',
            'status'         => 'required|in:active,inactive',
            'profile_photo'  => 'nullable|image|max:2048',
        ]);

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
            'message' => 'Password has been reset to default (password123) for ' . $user->full_name . '.',
        ]);
    }
}
