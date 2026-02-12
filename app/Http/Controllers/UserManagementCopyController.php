<?php
/*

namespace App\Http\Controllers;

use App\Helpers\TransactionHelper;
use App\Models\Role;
use App\Models\Warehouse;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserManagementController extends Controller
{
    public function index()
    {
        $employees = User::with('role')->get();
        $roles = Role::all();
        $warehouses = Warehouse::all();
        return view('settings.user-management.index', compact('employees', 'roles', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employee,email',
            'username' => 'required|unique:employee,username',
            'role_id' => 'required',
        ]);

        try {
            $path = null;
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
            }

            // Create User (which is also the Employee)
            User::create([
                'full_name'      => $request->full_name,
                'email'          => $request->email,
                'username'       => $request->username,
                'password'       => Hash::make('password123'), // Default password
                'contact_number' => $request->contact_number,
                'address'        => $request->address,
                'role_id'        => $request->role_id,
                'assigned_at'    => $request->assigned_at,
                'date_hired'     => $request->date_hired,
                'status'         => $request->status ?? 'active',
                'profile_photo'  => $path,
            ]);

            return response()->json(['success' => true, 'message' => 'New Employee Created Successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        // Find user (employee)
        $user = User::findOrFail($request->id);

        // Validation
        $request->validate([
            'email' => 'required|email|unique:employee,email,' . $user->id,
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:employee,username,' . $user->id,
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        // Image handling
        $profilePhotoPath = $user->profile_photo;
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        try {
            // Update user
            $user->update([
                'full_name'      => $request->full_name,
                'email'          => $request->email,
                'username'       => $request->username,
                'role_id'        => $request->role_id,
                'contact_number' => $request->contact_number,
                'address'        => $request->address,
                'date_hired'     => $request->date_hired,
                'status'         => $request->status,
                'profile_photo'  => $profilePhotoPath,
                'assigned_at'    => $request->assigned_at,
            ]);

            // Refresh session if updating current user
            if (auth()->id() == $user->id) {
                auth()->user()->refresh();
                session(['auth.user.name' => $user->full_name]);
            }

            return response()->json(['success' => true, 'message' => 'Updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ✅ ✅ ✅ FIXED: Update PIN (for NEW users setting PIN first time)
     */
    /*
    public function updatePin(Request $request)
    {
        // Convert array to string if needed
        if ($request->has('pin') && is_array($request->pin)) {
            $request->merge(['pin' => implode('', $request->pin)]);
        }

        $request->validate(['pin' => 'required|string|size:6']);

        $user = User::findOrFail(auth()->id());

        // Hash and save PIN
        $user->update(['pin' => Hash::make($request->pin)]);

        // ✅ Clear modal session and mark as verified
        session()->forget(['show_pin_modal', 'pin_mode']);
        session(['pin_verified' => true]);

        Log::info('PIN Set Successfully', [
            'user_id' => $user->id,
            'user_name' => $user->full_name
        ]);

        return response()->json(['success' => true, 'message' => 'PIN set successfully!']);
    }

    /**
     * ✅ ✅ ✅ FIXED: Verify PIN (for EXISTING users logging in)
     */
    /*
    public function verifyPin(Request $request)
    {
        // Convert array to string if needed
        if ($request->has('pin') && is_array($request->pin)) {
            $request->merge(['pin' => implode('', $request->pin)]);
        }

        $request->validate(['pin' => 'required|string|size:6']);

        $user = User::findOrFail(auth()->id());

        // Check if PIN matches
        if (Hash::check($request->pin, $user->pin)) {
            // ✅ Clear modal session and mark as verified
            session()->forget(['show_pin_modal', 'pin_mode']);
            session(['pin_verified' => true]);

            Log::info('PIN Verified Successfully', [
                'user_id' => $user->id,
                'user_name' => $user->full_name
            ]);

            return response()->json(['success' => true, 'message' => 'Access Granted!']);
        }

        Log::warning('PIN Verification Failed', [
            'user_id' => $user->id,
            'user_name' => $user->full_name
        ]);

        return response()->json(['success' => false, 'message' => 'Incorrect PIN!'], 422);
    }

    /**
     * ✅ ✅ ✅ FIXED: Reset PIN (Admin resets user's PIN)
     */
    /*
    public function resetPin(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);

            // Set PIN to null (user will need to set new PIN on next login)
            $user->update(['pin' => null]);

            Log::info('PIN Reset by Admin', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->full_name,
                'target_user_id' => $user->id,
                'target_user_name' => $user->full_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PIN has been reset for ' . $user->full_name
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            return TransactionHelper::run(function () use ($id) {
                $user = User::findOrFail($id);

                // Delete photo if exists
                if ($user->profile_photo) {
                    Storage::disk('public')->delete($user->profile_photo);
                }

                // Delete user
                $user->delete();

                return response()->json(['success' => true, 'message' => 'Deleted successfully!']);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}

feb 11