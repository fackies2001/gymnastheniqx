<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        // 🔍 DEBUG
        \Log::info('Profile Edit Method Called', [
            'user_id' => $user->id,
            'view' => 'profile.edit',
            'view_exists' => view()->exists('profile.edit'),
            'view_path' => view('profile.edit')->getPath(),
        ]);

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'           => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'profile_photo'  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $updateData = [
            'full_name'      => $request->name,
            'contact_number' => $request->contact_number,
        ];

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // ✅ CONSISTENT FOLDER NAME
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $updateData['profile_photo'] = $path;
        }

        $user->update($updateData);
        $user->refresh();
        session(['auth.user.name' => $user->name]);

        return redirect()->back()->with('status', 'profile-updated');
    }
}
